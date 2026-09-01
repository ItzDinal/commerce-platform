<?php

namespace Tests\Feature\Customer;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_checkout(): void
    {
        $response = $this->get(route('customer.checkout.index'));

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_authenticated_customer_can_access_checkout(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);

        $response = $this->actingAs($user)
            ->get(route('customer.checkout.index'));

        $response->assertSuccessful();
        $response->assertSee('Checkout');
    }

    public function test_checkout_displays_authenticated_customer_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Checkout Customer',
            'email' => 'checkout@example.com',
            'phone' => '+94771234567',
        ]);
        $this->addCartItem($user);

        $response = $this->actingAs($user)
            ->get(route('customer.checkout.index'));

        $response->assertSee('Checkout Customer');
        $response->assertSee('checkout@example.com');
        $response->assertSee('+94771234567');
    }

    public function test_empty_cart_cannot_checkout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('customer.checkout.index'));

        $response->assertRedirect(route('customer.cart.index'));
        $response->assertSessionHasErrors('cart');
    }

    public function test_customer_can_view_checkout_with_cart_items(): void
    {
        $user = User::factory()->create();
        $variant = $this->addCartItem($user, quantity: 2, price: 15000);

        $response = $this->actingAs($user)
            ->get(route('customer.checkout.index'));

        $response->assertSee($variant->product->name);
        $response->assertSee('Quantity: 2');
        $response->assertSee('Unit price: 15000');
        $response->assertSee('Line total: 30000');
        $response->assertSee('Subtotal: 30000');
        $response->assertSee('Shipping fee: 500');
        $response->assertSee('Total: 30500');
    }

    public function test_checkout_displays_the_standard_shipping_method(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);

        $response = $this->actingAs($user)
            ->get(route('customer.checkout.index'));

        $response->assertSee('Shipping Method');
        $response->assertSee('Standard Shipping');
        $response->assertSee('500 LKR');
    }

    public function test_checkout_requires_a_shipping_address(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'));

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('shipping_address_id');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_customer_cannot_checkout_using_another_customers_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->addCartItem($user);
        $otherAddress = $this->createAddress($otherUser);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'shipping_address_id' => $otherAddress->id,
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('shipping_address_id');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_checkout_shows_only_the_customers_own_addresses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->addCartItem($user);
        $ownAddress = $this->createAddress($user, [
            'address_line_1' => 'Own Customer Address',
        ]);
        $otherAddress = $this->createAddress($otherUser, [
            'address_line_1' => 'Other Customer Address',
        ]);

        $response = $this->actingAs($user)
            ->get(route('customer.checkout.index'));

        $response->assertSee($ownAddress->address_line_1);
        $response->assertDontSee($otherAddress->address_line_1);
    }

    public function test_customer_without_an_address_gets_a_validation_error(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);

        $this->actingAs($user)
            ->post(route('customer.checkout.store'))
            ->assertSessionHasErrors('shipping_address_id');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_checkout_rejects_an_invalid_email(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'name' => $user->name,
                'email' => 'not-an-email',
                'phone' => '+94771234567',
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('email');
        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_an_invalid_phone(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => 'invalid-phone',
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('phone');
        $this->assertNull($user->fresh()->phone);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_missing_required_customer_information(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'name' => '',
                'email' => '',
                'phone' => '',
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors(['name', 'email']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_updates_only_the_authenticated_customers_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $this->actingAs($user)->post(route('customer.checkout.store'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+94770001122',
            'shipping_address_id' => $address->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+94770001122',
        ]);
    }

    public function test_checkout_calculates_subtotal_correctly(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user, quantity: 2, price: 15000);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'subtotal' => 30000,
        ]);
    }

    public function test_checkout_calculates_shipping_fee_correctly(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $this->assertDatabaseHas('orders', [
            'shipping_fee' => 500,
        ]);
    }

    public function test_checkout_calculates_final_total_correctly(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user, quantity: 2, price: 15000);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $this->assertDatabaseHas('orders', [
            'subtotal' => 30000,
            'shipping_fee' => 500,
            'total' => 30500,
        ]);
    }

    public function test_selected_shipping_method_is_stored_on_the_order(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $this->actingAs($user)->post(route('customer.checkout.store'), [
            'shipping_address_id' => $address->id,
            'shipping_method' => 'standard',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'shipping_method' => 'standard',
            'shipping_fee' => 500,
        ]);
    }

    public function test_checkout_rejects_an_unsupported_shipping_method(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'shipping_address_id' => $address->id,
                'shipping_method' => 'overnight',
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('shipping_method');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_submitted_totals_cannot_change_the_server_side_order_totals(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user, quantity: 2, price: 15000);
        $address = $this->createAddress($user);

        $this->actingAs($user)->post(route('customer.checkout.store'), [
            'shipping_address_id' => $address->id,
            'shipping_method' => 'standard',
            'subtotal' => 1,
            'shipping_fee' => 1,
            'total' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'subtotal' => 30000,
            'shipping_fee' => 500,
            'total' => 30500,
        ]);
    }

    public function test_checkout_creates_an_order_successfully(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $response = $this->placeOrder($user, $address);

        $response->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'status' => 'pending',
        ]);
    }

    public function test_checkout_creates_the_correct_order_items(): void
    {
        $user = User::factory()->create();
        $variant = $this->addCartItem($user, quantity: 2, price: 15000);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $this->assertDatabaseHas('order_items', [
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'sku' => $variant->sku,
            'quantity' => 2,
            'unit_price' => 15000,
            'line_total' => 30000,
        ]);
    }

    public function test_checkout_generates_and_stores_an_order_number(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $order = Order::first();

        $this->assertNotNull($order?->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
        $this->assertDatabaseHas('orders', [
            'order_number' => $order->order_number,
        ]);
    }

    public function test_checkout_stores_integer_lkr_amounts(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user, quantity: 2, price: 15000);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $order = Order::firstOrFail();

        $this->assertIsInt($order->subtotal);
        $this->assertIsInt($order->shipping_fee);
        $this->assertIsInt($order->total);
        $this->assertSame(30000, $order->subtotal);
        $this->assertSame(500, $order->shipping_fee);
        $this->assertSame(30500, $order->total);
    }

    public function test_cart_is_cleared_after_successful_order_creation(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_cart_remains_intact_when_checkout_validation_fails(): void
    {
        $user = User::factory()->create();
        $variant = $this->addCartItem($user, quantity: 2);

        $this->actingAs($user)
            ->post(route('customer.checkout.store'), [])
            ->assertSessionHasErrors('shipping_address_id');

        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_insufficient_inventory(): void
    {
        $user = User::factory()->create();
        $variant = $this->addCartItem($user, quantity: 3, stock: 2);
        $address = $this->createAddress($user);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'shipping_address_id' => $address->id,
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('checkout');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_guest_cannot_place_an_order(): void
    {
        $response = $this->post(route('customer.checkout.store'));

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_can_view_their_success_page_but_not_another_customers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);
        $this->placeOrder($user, $address);
        $order = Order::firstOrFail();

        $this->actingAs($user)
            ->get(route('customer.checkout.success', $order))
            ->assertSuccessful()
            ->assertSee($order->order_number)
            ->assertSee('Purchased Items')
            ->assertSee('Shipping method: Standard Shipping');

        $this->actingAs($otherUser)
            ->get(route('customer.checkout.success', $order))
            ->assertForbidden();
    }

    public function test_order_numbers_are_unique(): void
    {
        $user = User::factory()->create();
        $firstAddress = $this->createAddress($user);
        $this->addCartItem($user);
        $this->placeOrder($user, $firstAddress);

        $secondAddress = $this->createAddress($user);
        $this->addCartItem($user);
        $this->placeOrder($user, $secondAddress);

        $this->assertDatabaseCount('orders', 2);
        $this->assertSame(
            2,
            Order::query()->distinct('order_number')->count('order_number')
        );
    }

    public function test_checkout_uses_shipping_address_as_billing_address(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $address = $this->createAddress($user);

        $this->placeOrder($user, $address);

        $order = Order::firstOrFail();

        $this->assertSame($address->id, $order->shipping_address_id);
        $this->assertSame($address->id, $order->billing_address_id);
    }

    public function test_checkout_can_use_a_separate_billing_address(): void
    {
        $user = User::factory()->create();
        $this->addCartItem($user);
        $shippingAddress = $this->createAddress($user);
        $billingAddress = $this->createAddress($user);

        $this->actingAs($user)->post(route('customer.checkout.store'), [
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
            'same_as_shipping' => '0',
            'shipping_method' => 'standard',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
        ]);
    }

    public function test_checkout_cannot_use_another_customers_billing_address(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->addCartItem($user);
        $shippingAddress = $this->createAddress($user);
        $otherBillingAddress = $this->createAddress($otherUser);

        $response = $this->actingAs($user)
            ->from(route('customer.checkout.index'))
            ->post(route('customer.checkout.store'), [
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $otherBillingAddress->id,
                'same_as_shipping' => '0',
                'shipping_method' => 'standard',
            ]);

        $response->assertRedirect(route('customer.checkout.index'));
        $response->assertSessionHasErrors('billing_address_id');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    private function addCartItem(
        User $user,
        int $quantity = 1,
        int $price = 15000,
        int $stock = 10
    ): ProductVariant {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => $price,
        ]);

        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $stock,
            'reserved' => 0,
        ]);

        CartItem::create([
            'cart_id' => $user->persistentCart()->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        return $variant->load('product');
    }

    private function createAddress(User $user, array $attributes = []): Address
    {
        return Address::factory()->create(array_merge([
            'user_id' => $user->id,
        ], $attributes));
    }

    private function placeOrder(User $user, Address $address)
    {
        return $this->actingAs($user)->post(
            route('customer.checkout.store'),
            ['shipping_address_id' => $address->id]
        );
    }
}
