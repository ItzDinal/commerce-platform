<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->middleware('admin')
            ->get('/test-admin', function () {
                return response('Admin area');
            });
    }

    public function test_guest_cannot_access_admin_area(): void
    {
        $response = $this->get('/test-admin');

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_admin_area(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($customer)
            ->get('/test-admin');

        $response->assertStatus(403);
    }

    public function test_inactive_admin_cannot_access_admin_area(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_INACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->get('/test-admin');

        $response->assertStatus(403);
    }

    public function test_active_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->get('/test-admin');

        $response->assertStatus(200);
        $response->assertSee('Admin area');
    }

    public function test_active_super_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->get('/test-admin');

        $response->assertStatus(200);
        $response->assertSee('Admin area');
    }
}