<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_guest_can_view_registration_page(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Account');
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.com',
        ]);

        $user = User::where('email', 'customer@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->getKeyType() === 'string');
        $this->assertFalse($user->getIncrementing());

        $this->assertTrue(
            Hash::check('password123', $user->password)
        );

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect();

        $this->assertGuest();
    }
    public function test_google_callback_creates_and_authenticates_user(): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);

        $googleUser->shouldReceive('getEmail')
            ->twice()
            ->andReturn('google@example.com');

        $googleUser->shouldReceive('getName')
            ->once()
            ->andReturn('Google Customer');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'name' => 'Google Customer',
        ]);

        $user = User::where('email', 'google@example.com')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_reuses_existing_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Existing Customer',
            'email' => 'google@example.com',
            'password' => 'existing-password',
        ]);

        $originalPassword = $user->password;

        $googleUser = Mockery::mock(SocialiteUser::class);

        $googleUser->shouldReceive('getEmail')
            ->once()
            ->andReturn('google@example.com');

        $googleUser->shouldReceive('getName')
            ->once()
            ->andReturn('Google Customer');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();

        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('home'));

        $this->assertDatabaseCount('users', 1);

        $user->refresh();

        $this->assertSame('Google Customer', $user->name);
        $this->assertSame($originalPassword, $user->password);
        $this->assertAuthenticatedAs($user);
    }
}