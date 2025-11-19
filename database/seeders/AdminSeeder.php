<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Monitoring Account
        User::updateOrCreate(
            ['email' => 'spectate@gmail.com'],
            [
                'name' => 'Spectate User',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_admin' => 1
            ]
        );

        // Official Account
        User::updateOrCreate(
            ['email' => 'civicwatch.brgy@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('civicwatch123'),
                'role' => 'admin',
                'is_admin' => 1
            ]
        );
    }
}
