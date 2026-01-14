<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if super admin already exists
        $existingAdmin = User::where('email', 'admin@rasagroup.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('Super Admin already exists!');
            $this->command->info('Email: admin@rasagroup.com');
            return;
        }

        // Create super admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@rasagroup.com',
            'password' => Hash::make('Admin@123'),
            'phone' => '081234567890',
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super Admin created successfully!');
        $this->command->info('================================');
        $this->command->info('Email    : admin@rasagroup.com');
        $this->command->info('Password : Admin@123');
        $this->command->info('ID       : ' . $admin->id);
        $this->command->info('================================');
        $this->command->info('Login URL: /admin/login');
    }
}
