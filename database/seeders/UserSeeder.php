<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Juan',
            'email' => 'juan@gmail.com',
            'password' => Hash::make('123456'),
        ]);

        User::create([
            'name' => 'Pedro',
            'email' => 'pedro@gmail.com',
            'password' => Hash::make('123456'),
        ]);
    }
}