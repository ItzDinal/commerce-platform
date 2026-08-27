<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            [
                'name' => 'Demo Customer 1',
                'email' => 'demo.customer1@example.com',
            ],
            [
                'name' => 'Demo Customer 2',
                'email' => 'demo.customer2@example.com',
            ],
            [
                'name' => 'Demo Customer 3',
                'email' => 'demo.customer3@example.com',
            ],
        ])->map(function (array $data) {
            return User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email_verified_at' => now(),
                    'password' => 'password',
                ]
            );
        });

        foreach ($users as $user) {
            $user->cart()->firstOrCreate([]);
        }
    }
}