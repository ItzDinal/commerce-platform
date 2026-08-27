<?php

namespace Tests\Feature\Customer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

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
    public function test_authenticated_customer_can_change_their_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPassword123!',
        ]);

        $response = $this->actingAs($user)
            ->put('/account/profile/password', [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect('/account/profile');

        $this->assertTrue(
            Hash::check('NewPassword123!', $user->fresh()->password)
        );
    }

    public function test_customer_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPassword123!',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile/password', [
                'current_password' => 'WrongPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updatePassword',
            'current_password'
        );

        $this->assertTrue(
            Hash::check('OldPassword123!', $user->fresh()->password)
        );
    }
    public function test_customer_cannot_change_password_when_confirmation_does_not_match(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPassword123!',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile/password', [
                'current_password' => 'OldPassword123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updatePassword',
            'password'
        );

        $this->assertTrue(
            Hash::check('OldPassword123!', $user->fresh()->password)
        );
    }
    public function test_customer_cannot_update_profile_without_a_name(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile', [
                'name' => '',
                'email' => 'john@example.com',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updateProfileInformation',
            'name'
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
    public function test_customer_cannot_update_profile_with_an_excessively_long_name(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile', [
                'name' => str_repeat('A', 256),
                'email' => 'john@example.com',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updateProfileInformation',
            'name'
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
    public function test_customer_cannot_update_profile_without_an_email(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->from('/account/profile')
            ->put('/account/profile', [
                'name' => 'John Doe',
                'email' => '',
            ]);

        $response->assertRedirect('/account/profile');

        $response->assertSessionHasErrorsIn(
            'updateProfileInformation',
            'email'
        );

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
    public function test_guest_cannot_update_customer_profile(): void
    {
        $response = $this->from('/login')
            ->put('/account/profile', [
                'name' => 'Hacker',
                'email' => 'hacker@example.com',
            ]);

        $response->assertRedirect('/login');
    }

}