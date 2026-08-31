<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_admin_password_page(): void
    {
        $response = $this->get(route('admin.password.edit'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_view_admin_password_page(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('admin.password.edit'));

        $response->assertStatus(403);
    }

    public function test_inactive_admin_cannot_view_admin_password_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_INACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.password.edit'));

        $response->assertStatus(403);
    }

    public function test_active_admin_can_view_admin_password_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.password.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.password.edit');
    }

    public function test_active_admin_can_change_password(): void
    {
        $admin = User::factory()->create([
            'password' => 'OldPassword123!',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->put(route('admin.password.update'), [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect(route('admin.password.edit'));

        $this->assertTrue(
            Hash::check(
                'NewPassword123!',
                $admin->fresh()->password
            )
        );
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $admin = User::factory()->create([
            'password' => 'OldPassword123!',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.password.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'WrongPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect(route('admin.password.edit'));

        $response->assertSessionHasErrorsIn(
            'updatePassword',
            'current_password'
        );

        $this->assertTrue(
            Hash::check(
                'OldPassword123!',
                $admin->fresh()->password
            )
        );
    }

    public function test_admin_cannot_change_password_when_confirmation_does_not_match(): void
    {
        $admin = User::factory()->create([
            'password' => 'OldPassword123!',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.password.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
            ]);

        $response->assertRedirect(route('admin.password.edit'));

        $response->assertSessionHasErrorsIn(
            'updatePassword',
            'password'
        );

        $this->assertTrue(
            Hash::check(
                'OldPassword123!',
                $admin->fresh()->password
            )
        );
    }
}