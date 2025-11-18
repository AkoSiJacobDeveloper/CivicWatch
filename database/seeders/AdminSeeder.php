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
        User::updateOrCreate(
            ['email' => 'civicwatch@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_admin' => 1
            ]
        );

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
