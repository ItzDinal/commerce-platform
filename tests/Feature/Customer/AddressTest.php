<?php

namespace Tests\Feature\Customer;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_addresses(): void
    {
        $response = $this->get('/account/addresses');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_addresses(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/account/addresses');

        $response->assertSuccessful();
    }

    public function test_authenticated_customer_can_view_add_address_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/account/addresses/create');

        $response->assertSuccessful();
        $response->assertSee('Add Address');
    }

    public function test_customer_can_add_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/account/addresses', [
                'label' => 'Home',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'company' => null,
                'address_line_1' => '123 Main Street',
                'address_line_2' => 'Apartment 4B',
                'city' => 'Colombo',
                'state' => 'Western',
                'postal_code' => '00100',
                'country' => 'Sri Lanka',
                'phone' => '+94112223344',
            ]);

        $response->assertRedirect('/account/addresses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'label' => 'Home',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '123 Main Street',
            'city' => 'Colombo',
            'postal_code' => '00100',
            'country' => 'Sri Lanka',
        ]);
    }

    public function test_customer_cannot_add_address_without_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/account/addresses/create')
            ->post('/account/addresses', []);

        $response->assertRedirect('/account/addresses/create');

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'address_line_1',
            'city',
            'state',
            'postal_code',
            'country',
        ]);
    }
    public function test_customer_cannot_add_address_with_oversized_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/account/addresses/create')
            ->post('/account/addresses', [
                'label' => str_repeat('A', 101),
                'first_name' => str_repeat('A', 101),
                'last_name' => str_repeat('B', 101),
                'company' => str_repeat('C', 151),
                'address_line_1' => str_repeat('D', 256),
                'address_line_2' => str_repeat('E', 256),
                'city' => str_repeat('F', 101),
                'state' => str_repeat('G', 101),
                'postal_code' => str_repeat('H', 21),
                'country' => str_repeat('I', 101),
                'phone' => str_repeat('1', 31),
            ]);

        $response->assertRedirect('/account/addresses/create');

        $response->assertSessionHasErrors([
            'label',
            'first_name',
            'last_name',
            'company',
            'address_line_1',
            'address_line_2',
            'city',
            'state',
            'postal_code',
            'country',
            'phone',
        ]);
    }

    public function test_guest_cannot_access_edit_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get("/account/addresses/{$address->id}/edit");

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_edit_form(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'city' => 'Colombo',
        ]);

        $response = $this->actingAs($user)
            ->get("/account/addresses/{$address->id}/edit");

        $response->assertSuccessful();
        $response->assertSee('Edit Address');
        $response->assertSee('John');
    }

    public function test_customer_can_update_their_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'city' => 'Colombo',
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$address->id}", [
                'label' => 'Work',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'company' => 'Acme',
                'address_line_1' => '456 Market Street',
                'address_line_2' => 'Floor 2',
                'city' => 'Kandy',
                'state' => 'Central',
                'postal_code' => '20000',
                'country' => 'Sri Lanka',
                'phone' => '+94112223355',
            ]);

        $response->assertRedirect('/account/addresses');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'label' => 'Work',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'city' => 'Kandy',
            'state' => 'Central',
        ]);
    }

    public function test_customer_update_requires_valid_data(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from("/account/addresses/{$address->id}/edit")
            ->put("/account/addresses/{$address->id}", [
                'label' => 'Home',
                'first_name' => '',
                'last_name' => '',
                'company' => 'Acme',
                'address_line_1' => '',
                'address_line_2' => 'Unit 7',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
                'phone' => '',
            ]);

        $response->assertRedirect("/account/addresses/{$address->id}/edit");
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'address_line_1',
            'city',
            'state',
            'postal_code',
            'country',
        ]);
    }

    public function test_customer_can_delete_their_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete("/account/addresses/{$address->id}");

        $response->assertRedirect('/account/addresses');
        $this->assertDatabaseMissing('addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_customer_cannot_edit_another_customers_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)
            ->get("/account/addresses/{$address->id}/edit");

        $response->assertForbidden();
    }

    public function test_customer_cannot_update_another_customers_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)
            ->from('/account/addresses')
            ->put("/account/addresses/{$address->id}", [
                'label' => 'Hijack',
                'first_name' => 'Bad',
                'last_name' => 'Actor',
                'address_line_1' => '999 Evil Street',
                'city' => 'Nowhere',
                'state' => 'Unknown',
                'postal_code' => '00000',
                'country' => 'World',
            ]);

        $response->assertForbidden();
    }

    public function test_customer_cannot_delete_another_customers_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)
            ->delete("/account/addresses/{$address->id}");

        $response->assertForbidden();
    }

    public function test_address_belongs_to_user_and_uses_ulid_and_boolean_casts(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => true,
            'is_default_billing' => false,
        ]);

        $this->assertTrue($address->user->is($user));
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $address->id);
        $this->assertTrue($address->is_default_shipping);
        $this->assertFalse($address->is_default_billing);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->addresses);
    }

    public function test_user_can_have_multiple_addresses(): void
    {
        $user = User::factory()->create();

        Address::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->fresh()->addresses);
        $this->assertSame([
            $user->id,
            $user->id,
        ], $user->fresh()->addresses()->pluck('user_id')->all());
    }
    public function test_customer_can_set_default_shipping_address(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$address->id}/default-shipping");

        $response->assertRedirect('/account/addresses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $user->id,
            'is_default_shipping' => true,
        ]);
    }
    public function test_setting_new_default_shipping_address_unsets_previous_default(): void
    {
        $user = User::factory()->create();

        $oldDefault = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => true,
        ]);

        $newDefault = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$newDefault->id}/default-shipping");

        $response->assertRedirect('/account/addresses');

        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefault->id,
            'is_default_shipping' => false,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $newDefault->id,
            'is_default_shipping' => true,
        ]);

        $this->assertSame(
            1,
            Address::where('user_id', $user->id)
                ->where('is_default_shipping', true)
                ->count()
        );
    }
    public function test_customer_cannot_set_another_customers_address_as_default_shipping(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'is_default_shipping' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$otherAddress->id}/default-shipping");

        $response->assertForbidden();

        $this->assertDatabaseHas('addresses', [
            'id' => $otherAddress->id,
            'user_id' => $otherUser->id,
            'is_default_shipping' => false,
        ]);
    }
    public function test_guest_cannot_set_default_shipping_address(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => false,
        ]);

        $response = $this->put(
            "/account/addresses/{$address->id}/default-shipping"
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'is_default_shipping' => false,
        ]);
    }
    public function test_setting_default_shipping_does_not_affect_another_customer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userAddress = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => false,
        ]);

        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'is_default_shipping' => true,
        ]);

        $this->actingAs($user)
            ->put("/account/addresses/{$userAddress->id}/default-shipping");

        $this->assertDatabaseHas('addresses', [
            'id' => $userAddress->id,
            'is_default_shipping' => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $otherAddress->id,
            'user_id' => $otherUser->id,
            'is_default_shipping' => true,
        ]);
    }
    public function test_customer_can_set_default_billing_address(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_billing' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$address->id}/default-billing");

        $response->assertRedirect('/account/addresses');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'user_id' => $user->id,
            'is_default_billing' => true,
        ]);
    }

    public function test_setting_new_default_billing_address_unsets_previous_default(): void
    {
        $user = User::factory()->create();

        $oldDefault = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_billing' => true,
        ]);

        $newDefault = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_billing' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$newDefault->id}/default-billing");

        $response->assertRedirect('/account/addresses');

        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefault->id,
            'is_default_billing' => false,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $newDefault->id,
            'is_default_billing' => true,
        ]);
    }

    public function test_customer_cannot_set_another_customers_address_as_default_billing(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'is_default_billing' => false,
        ]);

        $response = $this->actingAs($user)
            ->put("/account/addresses/{$otherAddress->id}/default-billing");

        $response->assertForbidden();

        $this->assertDatabaseHas('addresses', [
            'id' => $otherAddress->id,
            'user_id' => $otherUser->id,
            'is_default_billing' => false,
        ]);
    }

    public function test_guest_cannot_set_default_billing_address(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_billing' => false,
        ]);

        $response = $this->put(
            "/account/addresses/{$address->id}/default-billing"
        );

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'is_default_billing' => false,
        ]);
    }

    public function test_setting_default_billing_does_not_affect_shipping_default(): void
    {
        $user = User::factory()->create();

        $shippingAddress = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => true,
            'is_default_billing' => false,
        ]);

        $billingAddress = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ]);

        $this->actingAs($user)
            ->put("/account/addresses/{$billingAddress->id}/default-billing");

        $this->assertDatabaseHas('addresses', [
            'id' => $shippingAddress->id,
            'is_default_shipping' => true,
            'is_default_billing' => false,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $billingAddress->id,
            'is_default_shipping' => false,
            'is_default_billing' => true,
        ]);
    }

    public function test_setting_default_billing_does_not_affect_another_customer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userAddress = Address::factory()->create([
            'user_id' => $user->id,
            'is_default_billing' => false,
        ]);

        $otherAddress = Address::factory()->create([
            'user_id' => $otherUser->id,
            'is_default_billing' => true,
        ]);

        $this->actingAs($user)
            ->put("/account/addresses/{$userAddress->id}/default-billing");

        $this->assertDatabaseHas('addresses', [
            'id' => $userAddress->id,
            'is_default_billing' => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $otherAddress->id,
            'user_id' => $otherUser->id,
            'is_default_billing' => true,
        ]);
    }
}