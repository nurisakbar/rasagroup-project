<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Agent User
        $agent = User::firstOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name' => 'Agent User',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'role' => User::ROLE_AGENT,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('Agent User: agent@example.com / password (ID: ' . $agent->id . ')');

        // Create Buyer Users
        $buyers = [
            ['name' => 'John Buyer', 'email' => 'buyer@example.com', 'phone' => '081234567892'],
            ['name' => 'Jane Customer', 'email' => 'jane@example.com', 'phone' => '081234567893'],
            ['name' => 'Bob Shopper', 'email' => 'bob@example.com', 'phone' => '081234567894'],
        ];

        foreach ($buyers as $buyer) {
            $user = User::firstOrCreate(
                ['email' => $buyer['email']],
                [
                    'name' => $buyer['name'],
                    'password' => Hash::make('password'),
                    'phone' => $buyer['phone'],
                    'role' => User::ROLE_BUYER,
                    'email_verified_at' => now(),
                ]
            );
            $this->command->info('Buyer: ' . $buyer['email'] . ' / password (ID: ' . $user->id . ')');
        }

        // Create DRiiPPreneur User
        $driippreneur = User::firstOrCreate(
            ['email' => 'driippreneur@example.com'],
            [
                'name' => 'DRiiPPreneur User',
                'password' => Hash::make('password'),
                'phone' => '081234567895',
                'role' => User::ROLE_DRIIPPRENEUR,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('DRiiPPreneur: driippreneur@example.com / password (ID: ' . $driippreneur->id . ')');

        $this->command->info('');
        $this->command->info('All test users use password: password');
    }
}
