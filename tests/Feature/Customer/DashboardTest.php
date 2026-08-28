<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_customer_dashboard(): void
    {
        $response = $this->get('/account');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_customer_can_view_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->get('/account');

        $response->assertSuccessful();
        $response->assertSee('John Doe');
    }
}