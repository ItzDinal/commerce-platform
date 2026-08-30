<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_customer_is_not_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_CUSTOMER;

        $this->assertFalse($user->isAdmin());
    }

    public function test_admin_is_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_ADMIN;

        $this->assertTrue($user->isAdmin());
    }

    public function test_super_admin_is_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_SUPER_ADMIN;

        $this->assertTrue($user->isAdmin());
    }

    public function test_super_admin_is_super_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_SUPER_ADMIN;

        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_regular_admin_is_not_super_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_ADMIN;

        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_active_user_is_active(): void
    {
        $user = new User();
        $user->status = User::STATUS_ACTIVE;

        $this->assertTrue($user->isActive());
    }

    public function test_inactive_user_is_not_active(): void
    {
        $user = new User();
        $user->status = User::STATUS_INACTIVE;

        $this->assertFalse($user->isActive());
    }

    public function test_active_admin_is_active_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_ADMIN;
        $user->status = User::STATUS_ACTIVE;

        $this->assertTrue($user->isActiveAdmin());
    }

    public function test_inactive_admin_is_not_active_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_ADMIN;
        $user->status = User::STATUS_INACTIVE;

        $this->assertFalse($user->isActiveAdmin());
    }

    public function test_active_customer_is_not_active_admin(): void
    {
        $user = new User();
        $user->role = User::ROLE_CUSTOMER;
        $user->status = User::STATUS_ACTIVE;

        $this->assertFalse($user->isActiveAdmin());
    }
}