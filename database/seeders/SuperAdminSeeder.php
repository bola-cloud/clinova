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
        User::updateOrCreate(
            ['email' => 'superadmin@clinova.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Clinova@2026'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'subscription_active' => true,
            ]
        );
    }
}
