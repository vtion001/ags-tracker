<?php

namespace Tests\Feature;

use App\Models\ActiveBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverbreakMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_overbreaks_page_accessible_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/overbreaks');

        $response->assertStatus(200);
        $response->assertSee('Overbreak Monitor');
    }

    public function test_overbreaks_page_accessible_for_team_lead(): void
    {
        $tl = User::factory()->create(['role' => 'tl']);

        $response = $this->actingAs($tl)->get('/overbreaks');

        $response->assertStatus(200);
    }

    public function test_overbreaks_page_forbidden_for_agent(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->get('/overbreaks');

        $response->assertStatus(403);
    }

    public function test_overbreaks_live_data_returns_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent', 'tl_email' => $admin->email]);

        ActiveBreak::create([
            'break_id' => 'BRK-TEST123',
            'user_id' => $agent->id,
            'user_name' => $agent->name,
            'user_email' => $agent->email,
            'department' => 'Test',
            'tl_email' => $admin->email,
            'break_type' => '15m',
            'break_label' => '15-Minute Break',
            'allowed_minutes' => 15,
            'started_at' => now()->subMinutes(20),
            'expected_end_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($admin)->get('/overbreaks/live');

        $response->assertStatus(200);
        $response->assertJsonStructure(['on_break', 'overbreak', 'timestamp']);
    }

    public function test_overbreaks_separates_on_break_and_overbreak(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent1 = User::factory()->create(['role' => 'agent']);
        $agent2 = User::factory()->create(['role' => 'agent']);

        // Agent 1: on break (not over)
        ActiveBreak::create([
            'break_id' => 'BRK-ONBREAK',
            'user_id' => $agent1->id,
            'user_name' => $agent1->name,
            'user_email' => $agent1->email,
            'tl_email' => 'tl@test.com',
            'break_type' => '15m',
            'break_label' => '15-Minute Break',
            'allowed_minutes' => 15,
            'started_at' => now()->subMinutes(5),
            'expected_end_at' => now()->addMinutes(10),
        ]);

        // Agent 2: overbreak
        ActiveBreak::create([
            'break_id' => 'BRK-OVERBRK',
            'user_id' => $agent2->id,
            'user_name' => $agent2->name,
            'user_email' => $agent2->email,
            'tl_email' => 'tl@test.com',
            'break_type' => '15m',
            'break_label' => '15-Minute Break',
            'allowed_minutes' => 15,
            'started_at' => now()->subMinutes(20),
            'expected_end_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($admin)->get('/overbreaks/live');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data['on_break']);
        $this->assertCount(1, $data['overbreak']);
    }
}
