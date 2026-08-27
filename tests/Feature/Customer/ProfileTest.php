<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_guest_cannot_view_customer_profile(): void
    {
        $response = $this->get('/account/profile');

        $response->assertRedirect('/login');
    }
}