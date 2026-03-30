<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    private string $webhookUrl;
    private string $channel;

    public function __construct()
    {
        $this->webhookUrl = config('services.slack.webhook_url');
        $this->channel = config('services.slack.channel', '#alerts');
    }

    public function isConfigured(): bool
    {
        return !empty($this->webhookUrl);
    }

    public function sendOverbreakAlert(array $agents): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Slack webhook URL not configured');
            return false;
        }

        if (empty($agents)) {
            return false;
        }

        try {
            // Group agents by team lead email
            $byTeamLead = [];
            foreach ($agents as $agent) {
                $tlEmail = $agent['tl_email'] ?? 'unknown';
                if (!isset($byTeamLead[$tlEmail])) {
                    $byTeamLead[$tlEmail] = [];
                }
                $byTeamLead[$tlEmail][] = $agent;
            }

            // Build the message
            $blocks = [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => '🚨 Overbreak Alert',
                        'emoji' => true,
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => 'The following agents have exceeded their allowable break time and need immediate attention:',
                    ],
                ],
                ['type' => 'divider'],
            ];

            // Add each team lead's agents
            foreach ($byTeamLead as $tlEmail => $tlAgents) {
                $agentList = implode("\n", array_map(function ($agent) {
                    $name = $agent['user_name'] ?? 'Unknown';
                    $minutes = $agent['over_minutes'] ?? 0;
                    $dept = $agent['department'] ?? 'N/A';
                    return "• <{$agent['user_email']}|{$name}> *({$dept})* - {$minutes} min overbreak";
                }, $tlAgents));

                $blocks[] = [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => "*Team Lead: {$tlEmail}*\n" . $agentList,
                    ],
                ];
            }

            $blocks[] = ['type' => 'divider'];
            $blocks[] = [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'mrkdwn',
                        'text' => 'AGS Break Tracker | This channel receives automatic overbreak notifications.',
                    ],
                ],
            ];

            $payload = [
                'channel' => $this->channel,
                'username' => 'AGS Break Tracker',
                'icon_emoji' => ':warning:',
                'blocks' => $blocks,
            ];

            $response = Http::post($this->webhookUrl, $payload);

            if (!$response->successful()) {
                Log::error('Slack notification failed: ' . $response->status() . ' - ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('SlackService error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendTestNotification(): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('Slack webhook URL not configured');
            return false;
        }

        try {
            $payload = [
                'channel' => $this->channel,
                'username' => 'AGS Break Tracker',
                'icon_emoji' => ':white_check_mark:',
                'text' => '✅ Slack integration test successful! AGS Break Tracker alerts are now active.',
            ];

            $response = Http::post($this->webhookUrl, $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SlackService test error: ' . $e->getMessage());
            return false;
        }
    }
}
