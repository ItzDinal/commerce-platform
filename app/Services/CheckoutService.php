<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutService
{
    public const STANDARD_SHIPPING_METHOD = 'standard';

    public const STANDARD_SHIPPING_METHOD_NAME = 'Standard Shipping';

    public const SHIPPING_FEE = 500;

    public function quote(
        Cart $cart,
        string $shippingMethod = self::STANDARD_SHIPPING_METHOD
    ): array
    {
        $shippingFee = $this->shippingFee($shippingMethod);

        $cart->load([
            'items.productVariant.product',
            'items.productVariant.attributeValues',
        ]);

        $items = $cart->items->map(function ($item): array {
            $variant = $item->productVariant;
            $unitPrice = $variant->priceInLkr();

            return [
                'productName' => $variant->product->name,
                'variantName' => $this->variantName($variant),
                'sku' => $variant->sku,
                'quantity' => $item->quantity,
                'unitPrice' => $unitPrice,
                'lineTotal' => $unitPrice * $item->quantity,
            ];
        })->all();

        $subtotal = array_sum(array_column($items, 'lineTotal'));

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shippingMethod' => $shippingMethod,
            'shippingMethodName' => $this->shippingMethodName($shippingMethod),
            'shippingFee' => $shippingFee,
            'total' => $subtotal + $shippingFee,
        ];
    }

    public function createOrder(
        User $user,
        string $shippingAddressId,
        array $customerInformation = [],
        ?string $billingAddressId = null,
        string $shippingMethod = self::STANDARD_SHIPPING_METHOD
    ): Order
    {
        return DB::transaction(function () use (
            $user,
            $shippingAddressId,
            $customerInformation,
            $billingAddressId,
            $shippingMethod
        ): Order {
            // If no separate billing address was supplied,
            // use the shipping address as the billing address.
            $billingAddressId ??= $shippingAddressId;

            if (! $user->addresses()->whereKey($shippingAddressId)->exists()) {
                throw new RuntimeException('Invalid shipping address.');
            }

            if (! $user->addresses()->whereKey($billingAddressId)->exists()) {
                throw new RuntimeException('Invalid billing address.');
            }

            $cart = $user->persistentCart();

            $items = $cart->items()
                ->with([
                    'productVariant.product',
                    'productVariant.attributeValues',
                ])
                ->get();

            if ($items->isEmpty()) {
                throw new RuntimeException('Your cart is empty.');
            }

            $subtotal = 0;
            $snapshots = [];

            foreach ($items as $item) {
                $variant = ProductVariant::query()
                    ->with(['product', 'attributeValues'])
                    ->findOrFail($item->product_variant_id);

                $inventory = $variant->inventory()
                    ->lockForUpdate()
                    ->first();

                if (! $inventory || $item->quantity > $inventory->available) {
                    throw new RuntimeException(
                        'Insufficient available inventory for '.$variant->product->name.'.'
                    );
                }

                $unitPrice = $variant->priceInLkr();
                $lineTotal = $unitPrice * $item->quantity;

                $subtotal += $lineTotal;

                $snapshots[] = [
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $this->variantName($variant),
                    'sku' => $variant->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            $shippingFee = $this->shippingFee($shippingMethod);

            if ($customerInformation !== []) {
                $user->update($customerInformation);
            }

            $order = $user->orders()->create([
                'order_number' => $this->nextOrderNumber(),
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $billingAddressId,
                'shipping_method' => $shippingMethod,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
            ]);

            $order->items()->createMany($snapshots);

            $cart->items()->delete();

            return $order->load('items');
        });
    }

    private function variantName(ProductVariant $variant): string
    {
        return $variant->attributeValues
            ->pluck('value')
            ->implode(' / ');
    }

    private function shippingFee(string $shippingMethod): int
    {
        if ($shippingMethod !== self::STANDARD_SHIPPING_METHOD) {
            throw new RuntimeException('Invalid shipping method.');
        }

        return self::SHIPPING_FEE;
    }

    public function shippingMethodName(string $shippingMethod): string
    {
        $this->shippingFee($shippingMethod);

        return self::STANDARD_SHIPPING_METHOD_NAME;
    }

    private function nextOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.Str::upper(Str::random(12));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
