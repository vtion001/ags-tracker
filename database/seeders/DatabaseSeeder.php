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
            'tl_email' => 'admin@ags.com',
        ]);

        // Create team lead
        User::create([
            'name' => 'Sarah Team Lead',
            'email' => 'sarah.tl@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'tl',
            'department' => 'Sales',
            'tl_email' => 'sarah.tl@ags.com',
        ]);

        // Create agents
        User::create([
            'name' => 'John Agent',
            'email' => 'john.agent@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'agent',
            'department' => 'Sales',
            'tl_email' => 'sarah.tl@ags.com',
        ]);

        User::create([
            'name' => 'Jane Agent',
            'email' => 'jane.agent@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'agent',
            'department' => 'Sales',
            'tl_email' => 'sarah.tl@ags.com',
        ]);

        User::create([
            'name' => 'Bob Agent',
            'email' => 'bob.agent@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'agent',
            'department' => 'Support',
            'tl_email' => 'sarah.tl@ags.com',
        ]);
    }
}
