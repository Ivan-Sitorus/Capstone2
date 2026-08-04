<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insertOrIgnore([
            [
                'name' => 'Admin W9',
                'email' => 'admin@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ahmad Kasir',
                'email' => 'kasir@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Kasir',
                'email' => 'siti@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Kasir',
                'email' => 'budi@w9cafe.com',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
