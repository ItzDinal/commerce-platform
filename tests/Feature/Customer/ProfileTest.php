<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_customer_profile(): void
    {
        $response = $this->get('/account/profile');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_their_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->get('/account/profile');

        $response->assertSuccessful();
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
    }

    public function test_authenticated_customer_can_update_their_name(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->put('/account/profile', [
                'name' => 'Jane Doe',
                'email' => 'john@example.com',
            ]);

        $response->assertRedirect('/account/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_authenticated_customer_can_update_their_email(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->put('/account/profile', [
                'name' => 'John Doe',
                'email' => 'jane@example.com',
            ]);

        $response->assertRedirect('/account/profile');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_customer_cannot_update_to_an_existing_email(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile', [
                'name' => 'John Doe',
                'email' => 'jane@example.com',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updateProfileInformation',
            'email'
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'john@example.com',
        ]);
    }

    public function test_customer_cannot_update_to_an_invalid_email(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile', [
                'name' => 'John Doe',
                'email' => 'not-an-email',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updateProfileInformation',
            'email'
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'john@example.com',
        ]);
    }
}