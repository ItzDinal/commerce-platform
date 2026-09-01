<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Data\CartData;
use App\Data\CartItemData;

class CartService
{
    public function addToCart(
        Cart $cart,
        ProductVariant $variant,
        int $quantity
    ): CartItem {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Cart quantity must be greater than zero.'
            );
        }

        $inventory = $variant->inventory;

        if (! $inventory) {
            throw new RuntimeException(
                'Product variant has no inventory.'
            );
        }

        $existingItem = $cart->items()
            ->where('product_variant_id', $variant->id)
            ->first();

        $newQuantity = ($existingItem?->quantity ?? 0) + $quantity;

        if ($newQuantity > $inventory->available) {
            throw new RuntimeException(
                'Insufficient available inventory.'
            );
        }

        return DB::transaction(function () use (
            $cart,
            $variant,
            $quantity,
            $existingItem
        ) {
            if ($existingItem) {
                $existingItem->increment('quantity', $quantity);

                return $existingItem->fresh();
            }

            return $cart->items()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $quantity,
            ]);
        });
    }

    public function updateQuantity(
        CartItem $item,
        int $quantity
    ): CartItem {
        if ($quantity <= 0) {
            throw new RuntimeException(
                'Cart quantity must be greater than zero.'
            );
        }

        $inventory = $item->productVariant->inventory;

        if (! $inventory) {
            throw new RuntimeException(
                'Product variant has no inventory.'
            );
        }

        if ($quantity > $inventory->available) {
            throw new RuntimeException(
                'Insufficient available inventory.'
            );
        }

        $item->update([
            'quantity' => $quantity,
        ]);

        return $item->fresh();
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }
    public function getCart(Cart $cart): Cart
    {
        return $cart->load([
            'items.productVariant.product',
            'items.productVariant.attributeValues',
        ]);
    }
    public function totalItems(Cart $cart): int
    {
        return $cart->items->sum('quantity');
    }

    public function subtotal(Cart $cart): int
    {
        return $cart->items->sum(
            fn (CartItem $item) => $item->lineTotal()
        );
    }
    public function getCartData(Cart $cart): CartData
    {
        $cart->load([
            'items.productVariant.product',
            'items.productVariant.attributeValues',
        ]);

        $items = $cart->items->map(
            function (CartItem $item): CartItemData {
                $variant = $item->productVariant;

                $variantName = $variant->attributeValues
                    ->pluck('value')
                    ->implode(' / ');

                return new CartItemData(
                    id: $item->id,
                    productName: $variant->product->name,
                    variantName: $variantName,
                    sku: $variant->sku,
                    quantity: $item->quantity,
                    unitPrice: $variant->priceInLkr(),
                    lineTotal: $item->lineTotal(),
                );
            }
        )->all();

        return new CartData(
            id: $cart->id,
            items: $items,
            totalItems: $this->totalItems($cart),
            subtotal: $this->subtotal($cart),
        );
    }
}
