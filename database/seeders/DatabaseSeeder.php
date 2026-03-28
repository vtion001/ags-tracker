<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'AGS Admin',
            'email' => 'admin@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'admin',
            'department' => 'Administration',
        ]);
    }
}
