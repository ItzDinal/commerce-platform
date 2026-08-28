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
}