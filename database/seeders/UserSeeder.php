<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


public function run(): void
{
    User::create([
        'name' => 'Item Owner',
        'email' => 'owner@test.com',
        'password' => Hash::make('password'),
    ]);

    User::create([
        'name' => 'Renter User',
        'email' => 'renter@test.com',
        'password' => Hash::make('password'),
    ]);
}

}
