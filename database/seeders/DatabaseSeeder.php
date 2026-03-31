<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create teams
        $salesTeam = Team::create([
            'name' => 'Sales Team',
            'description' => 'Handles sales inquiries and customer acquisition',
            'color' => '#10b981',
        ]);

        $supportTeam = Team::create([
            'name' => 'Support Team',
            'description' => 'Provides customer support and issue resolution',
            'color' => '#3b82f6',
        ]);

        $billingTeam = Team::create([
            'name' => 'Billing Team',
            'description' => 'Handles billing inquiries and payment processing',
            'color' => '#f59e0b',
        ]);

        // Create admin user
        User::create([
            'name' => 'AGS Admin',
            'email' => 'admin@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'admin',
            'department' => 'Administration',
            'tl_email' => 'admin@ags.com',
            'status' => 'active',
            'onboarding_completed' => true,
        ]);

        // Create team lead
        $sarah = User::create([
            'name' => 'Sarah Team Lead',
            'email' => 'sarah.tl@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'tl',
            'department' => 'Sales',
            'tl_email' => 'sarah.tl@ags.com',
            'status' => 'active',
            'onboarding_completed' => true,
        ]);

        // Create agents
        User::create([
            'name' => 'John Agent',
            'email' => 'john.agent@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'agent',
            'department' => 'Sales',
            'team_id' => $salesTeam->id,
            'tl_email' => 'sarah.tl@ags.com',
            'status' => 'active',
            'onboarding_completed' => true,
        ]);

        User::create([
            'name' => 'Jane Agent',
            'email' => 'jane.agent@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'agent',
            'department' => 'Sales',
            'team_id' => $salesTeam->id,
            'tl_email' => 'sarah.tl@ags.com',
            'status' => 'active',
            'onboarding_completed' => true,
        ]);

        User::create([
            'name' => 'Bob Agent',
            'email' => 'bob.agent@ags.com',
            'password' => Hash::make('agent707'),
            'role' => 'agent',
            'department' => 'Support',
            'team_id' => $supportTeam->id,
            'tl_email' => 'sarah.tl@ags.com',
            'status' => 'active',
            'onboarding_completed' => true,
        ]);
    }
}
