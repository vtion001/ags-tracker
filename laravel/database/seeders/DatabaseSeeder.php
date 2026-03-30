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

        // Create team leads
        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah.johnson@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'tl',
            'department' => 'Operations',
        ]);

        User::create([
            'name' => 'Mike Chen',
            'email' => 'mike.chen@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'tl',
            'department' => 'Sales',
        ]);

        // Create agents
        $agents = [
            ['name' => 'John Doe', 'email' => 'john.doe@ags.com', 'department' => 'Sales'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@ags.com', 'department' => 'Operations'],
            ['name' => 'Bob Wilson', 'email' => 'bob.wilson@ags.com', 'department' => 'Support'],
            ['name' => 'Alice Brown', 'email' => 'alice.brown@ags.com', 'department' => 'Sales'],
            ['name' => 'Charlie Davis', 'email' => 'charlie.davis@ags.com', 'department' => 'Support'],
        ];

        foreach ($agents as $agent) {
            User::create([
                'name' => $agent['name'],
                'email' => $agent['email'],
                'password' => Hash::make('agent707'),
                'role' => 'agent',
                'department' => $agent['department'],
            ]);
        }
    }
}
