<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Idempotent: safe to run multiple times (uses updateOrCreate / firstOrCreate).
     */
    public function run(): void
    {
        // ---- Users (won't duplicate; password reset to "password") ----
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => 'password',          // auto-hashed by the User model cast
                'role'     => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name'     => 'Employee User',
                'password' => 'password',
                'role'     => User::ROLE_EMPLOYEE,
            ]
        );

        // ---- Sample products ----
        foreach (['Sample Product A', 'Sample Product B', '4x8 2mm HRC'] as $name) {
            Product::firstOrCreate(['name' => $name], ['is_deleted' => false]);
        }

        // ---- Sample customers ----
        Customer::firstOrCreate(
            ['name' => 'Ali Traders'],
            ['city' => 'Karachi', 'opening_balance' => 0, 'is_deleted' => false]
        );
        Customer::firstOrCreate(
            ['name' => 'Bismillah Steel'],
            ['city' => 'Lahore', 'opening_balance' => 0, 'is_deleted' => false]
        );
    }
}
