<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Create the initial super admin account.
     */
    public function run(): void
    {
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $name || ! $email || ! $password) {
            throw new RuntimeException(
                'ADMIN_NAME, ADMIN_EMAIL and ADMIN_PASSWORD must be configured.'
            );
        }

        $user = User::firstOrNew([
            'email' => $email,
        ]);

        if (! $user->exists) {
            $user->name = $name;
            $user->password = $password;
        }

        $user->role = User::ROLE_SUPER_ADMIN;
        $user->status = User::STATUS_ACTIVE;
        $user->email_verified_at ??= now();

        $user->save();
    }
}