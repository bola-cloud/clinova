<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin Account
        User::updateOrCreate(
            ['email' => 'admin@clinova.com'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'subscription_active' => true,
            ]
        );

        // Doctor Account
        $doctor = User::updateOrCreate(
            ['email' => 'doctor@clinova.com'],
            [
                'name' => 'Dr. Ahmed Ali',
                'password' => bcrypt('password'),
                'role' => 'doctor',
                'email_verified_at' => now(),
                'subscription_active' => true,
                'subscription_expires_at' => now()->addYear(),
            ]
        );

        // Secretary Account
        User::updateOrCreate(
            ['email' => 'secretary@clinova.com'],
            [
                'name' => 'Sara Secretary',
                'password' => bcrypt('password'),
                'role' => 'secretary',
                'doctor_id' => $doctor->id,
                'email_verified_at' => now(),
                'subscription_active' => true,
            ]
        );
    }
}
