<?php

namespace Tests\Feature\Customer;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Order Details Access
    |--------------------------------------------------------------------------
    */

    public function test_guest_cannot_view_order_details(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(
            route('customer.orders.show', $order)
        );

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_their_own_order(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('customer.orders.show', $order));

        $response->assertSuccessful();
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('customer.orders.show', $order));

        $response->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Order Information
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_order_number(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-123456',
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('ORD-123456');
    }

    public function test_order_details_display_current_status(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PROCESSING,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee(OrderStatus::PROCESSING->label());
    }

    /*
    |--------------------------------------------------------------------------
    | Order Items
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_order_items(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $product = Product::factory()->create([
            'name' => 'Premium Saree',
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SAREE-RED-001',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Premium Saree',
            'variant_name' => 'Red / Large',
            'sku' => 'SAREE-RED-001',
            'quantity' => 2,
            'unit_price' => 15000,
            'line_total' => 30000,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('Premium Saree')
            ->assertSee('Red / Large')
            ->assertSee('SAREE-RED-001');
    }

    public function test_order_details_display_item_quantity_and_prices(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => 'Default',
            'sku' => $variant->sku,
            'quantity' => 2,
            'unit_price' => 15000,
            'line_total' => 30000,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('2')
            ->assertSee('15,000')
            ->assertSee('30,000');
    }

    /*
    |--------------------------------------------------------------------------
    | Order Totals
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_order_totals(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 30000,
            'shipping_fee' => 500,
            'total' => 30500,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('30,000')
            ->assertSee('500')
            ->assertSee('30,500');
    }

    /*
    |--------------------------------------------------------------------------
    | Shipping Method
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_shipping_method(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'shipping_method' => 'standard',
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('Standard Shipping');
    }

    /*
    |--------------------------------------------------------------------------
    | Shipping Address
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_shipping_address(): void
    {
        $user = User::factory()->create();

        $shippingAddress = Address::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main Street',
            'city' => 'Colombo',
            'state' => 'Western',
            'postal_code' => '00100',
            'country' => 'Sri Lanka',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'shipping_address_id' => $shippingAddress->id,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('John')
            ->assertSee('Doe')
            ->assertSee('123 Main Street')
            ->assertSee('Colombo')
            ->assertSee('Western')
            ->assertSee('00100')
            ->assertSee('Sri Lanka');
    }

    /*
    |--------------------------------------------------------------------------
    | Billing Address
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_billing_address(): void
    {
        $user = User::factory()->create();

        $billingAddress = Address::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line_1' => '456 Billing Road',
            'city' => 'Kandy',
            'state' => 'Central',
            'postal_code' => '20000',
            'country' => 'Sri Lanka',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'billing_address_id' => $billingAddress->id,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('Jane')
            ->assertSee('Doe')
            ->assertSee('456 Billing Road')
            ->assertSee('Kandy')
            ->assertSee('Central')
            ->assertSee('20000')
            ->assertSee('Sri Lanka');
    }

    /*
    |--------------------------------------------------------------------------
    | Same Shipping / Billing Address
    |--------------------------------------------------------------------------
    */

    public function test_order_details_can_display_same_shipping_and_billing_address(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Same',
            'last_name' => 'Address',
            'address_line_1' => '789 Same Street',
            'city' => 'Galle',
            'state' => 'Southern',
            'postal_code' => '80000',
            'country' => 'Sri Lanka',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('789 Same Street')
            ->assertSee('Galle');
    }

    /*
    |--------------------------------------------------------------------------
    | Tracking Status
    |--------------------------------------------------------------------------
    */

    public function test_order_details_display_all_tracking_statuses(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PROCESSING,
        ]);

        $response = $this->actingAs($user)
            ->get(route('customer.orders.show', $order));

        $response->assertSuccessful();

        foreach (OrderStatus::cases() as $status) {
            $response->assertSee($status->label());
        }
    }

    public function test_pending_order_displays_order_placed_as_current_status(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('Order Placed');
    }

    public function test_delivered_order_displays_delivered_status(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::DELIVERED,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertSee('Delivered');
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Cannot Modify Order
    |--------------------------------------------------------------------------
    */

    public function test_customer_order_details_page_does_not_provide_status_modification(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PROCESSING,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders.show', $order))
            ->assertSuccessful()
            ->assertDontSee('Update Status')
            ->assertDontSee('Change Status');
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Cancellation
    |--------------------------------------------------------------------------
    */

    public function test_customer_cannot_cancel_order_through_the_backend(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PROCESSING,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('customer.orders.show', $order));

        $response->assertMethodNotAllowed();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::PROCESSING->value,
        ]);
    }
}