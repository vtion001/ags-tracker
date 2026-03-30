<?php

namespace App\Console\Commands;

use App\Models\ActiveBreak;
use App\Services\ElevenLabsService;
use App\Services\SlackService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckOverbreaks extends Command
{
    protected $signature = 'alerts:check-overbreaks';
    protected $description = 'Check for overbreak agents and trigger voice alerts + Slack notifications';

    private const OVERBREAK_COOLDOWN_MINUTES = 5; // Don't alert same agent more than once every 5 minutes

    public function handle(ElevenLabsService $elevenLabs, SlackService $slack): int
    {
        $this->info('Checking for overbreaks...');

        // Get all active breaks that are overbreak
        $overbreaks = ActiveBreak::where('expected_end_at', '<', now())->get();

        if ($overbreaks->isEmpty()) {
            $this->info('No overbreaks detected.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overbreaks->count()} overbreak(s)." );

        // Filter to only those not recently flagged
        $agentsToAlert = [];
        foreach ($overbreaks as $break) {
            $cacheKey = "overbreak_flagged_{$break->break_id}";

            if (Cache::has($cacheKey)) {
                $this->line("Agent {$break->user_name} was recently flagged, skipping.");
                continue;
            }

            $agentsToAlert[] = [
                'break_id' => $break->break_id,
                'user_name' => $break->user_name,
                'user_email' => $break->user_email,
                'over_minutes' => (int) floor(now()->diffInMinutes($break->expected_end_at)),
                'department' => $break->department,
                'tl_email' => $break->tl_email,
            ];

            // Mark as flagged with cooldown
            Cache::put($cacheKey, true, self::OVERBREAK_COOLDOWN_MINUTES * 60);
        }

        if (empty($agentsToAlert)) {
            $this->info('No new agents to alert.');
            return Command::SUCCESS;
        }

        $this->info('Alerting ' . count($agentsToAlert) . ' agent(s)...');

        // Send Slack notification
        if ($slack->isConfigured()) {
            $this->info('Sending Slack notification...');
            $slackResult = $slack->sendOverbreakAlert($agentsToAlert);

            if ($slackResult) {
                $this->info('Slack notification sent successfully.');
            } else {
                $this->warn('Failed to send Slack notification.');
            }
        } else {
            $this->warn('Slack not configured, skipping Slack notification.');
        }

        // Store pending alert metadata (not audio - audio generated on-demand)
        if ($elevenLabs->isConfigured()) {
            $cacheKey = 'pending_audio_alerts';
            $existingAlerts = Cache::get($cacheKey, []);

            // Add new agents to pending alerts (avoid duplicates)
            $existingNames = array_column($existingAlerts, 'agent_name');
            foreach ($agentsToAlert as $agent) {
                if (!in_array($agent['user_name'], $existingNames)) {
                    // Generate 3 warning clips for this agent
                    for ($i = 0; $i < 3; $i++) {
                        $existingAlerts[] = [
                            'agent_name' => $agent['user_name'],
                            'over_minutes' => $agent['over_minutes'],
                            'warning_number' => $i + 1,
                        ];
                    }
                }
            }

            Cache::put($cacheKey, $existingAlerts, 300); // Keep for 5 minutes
            $this->info('Queued ' . count($existingAlerts) . ' voice alert(s) for playback.');
        } else {
            $this->warn('ElevenLabs not configured, skipping voice alerts.');
        }

        // Log the alerting
        Log::info('Overbreak alerts triggered', [
            'agents' => $agentsToAlert,
            'slack_sent' => $slack->isConfigured(),
            'voice_alerts' => $elevenLabs->isConfigured(),
        ]);

        return Command::SUCCESS;
    }
}
