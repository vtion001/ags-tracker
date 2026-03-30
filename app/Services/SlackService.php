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
            $agentList = implode("\n", array_map(function ($agent) {
                $name = $agent['user_name'] ?? 'Unknown';
                $minutes = $agent['over_minutes'] ?? 0;
                return "• *{$name}* - {$minutes} minutes overbreak";
            }, $agents));

            $payload = [
                'channel' => $this->channel,
                'username' => 'AGS Break Tracker',
                'icon_emoji' => ':warning:',
                'attachments' => [
                    [
                        'color' => '#dc2626',
                        'title' => '🚨 Overbreak Alert',
                        'text' => "The following agents are on overbreak and need immediate attention:",
                        'fields' => [
                            [
                                'value' => $agentList,
                                'short' => false,
                            ]
                        ],
                        'footer' => 'AGS Break Tracker',
                        'ts' => time(),
                    ]
                ],
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
