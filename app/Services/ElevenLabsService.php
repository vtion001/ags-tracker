<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElevenLabsService
{
    private string $apiKey;
    private string $voiceId;
    private string $model;

    // Different alert scripts that call out each agent 3 times
    private array $alertScripts = [
        "Attention. Urgent alert. Agent {name} has exceeded their allowed break time. Please report back to your station immediately. This is your first warning.",
        "Alert. Agent {name} is still on overbreak. {name}, your break has exceeded the allowed duration. Return to your workstation now. Second warning issued.",
        "Final notice. Agent {name}, you have been on overbreak for an extended period. {name}, this is your third and final warning. Report to your supervisor immediately.",
    ];

    public function __construct()
    {
        $this->apiKey = config('elevenlabs.api_key');
        $this->voiceId = config('elevenlabs.voice_id');
        $this->model = config('elevenlabs.model');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->voiceId);
    }

    public function generateSpeech(string $text): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('ElevenLabs API key or voice ID not configured');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
            ])
            ->timeout(10)
            ->post("https://api.elevenlabs.io/v1/text-to-speech/{$this->voiceId}", [
                'text' => $text,
                'model_id' => $this->model,
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                    'style' => 0.0,
                    'use_speaker_boost' => true,
                ],
            ]);

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('ElevenLabs API error: ' . $response->status() . ' - ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('ElevenLabsService error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateOverbreakAlert(string $agentName, int $overMinutes): ?string
    {
        $text = "Alert. Agent {$agentName} has exceeded their break time by {$overMinutes} minutes. Please take action.";
        return $this->generateSpeech($text);
    }

    /**
     * Generate multiple audio clips for batch overbreak alert.
     * Each agent gets called out 3 times with different warning scripts.
     *
     * @param array $overbreakAgents Array of agents with 'user_name' and 'over_minutes'
     * @return array Array of audio blobs keyed by agent name and warning number
     */
    public function generateOverbreakBatchAlerts(array $overbreakAgents): array
    {
        if (empty($overbreakAgents)) {
            return [];
        }

        $audioClips = [];

        foreach ($overbreakAgents as $agent) {
            $name = $agent['user_name'] ?? 'Unknown';
            $overMinutes = $agent['over_minutes'] ?? 0;

            for ($i = 0; $i < 3; $i++) {
                $script = str_replace('{name}', $name, $this->alertScripts[$i]);
                $audio = $this->generateSpeech($script);

                if ($audio !== null) {
                    $audioClips[] = [
                        'agent_name' => $name,
                        'over_minutes' => $overMinutes,
                        'warning_number' => $i + 1,
                        'audio' => $audio,
                    ];
                }
            }
        }

        return $audioClips;
    }

    public function generateOverbreakBatchAlert(array $overbreakAgents): ?string
    {
        if (empty($overbreakAgents)) {
            return null;
        }

        // Generate a combined alert message
        $messages = [];
        foreach ($overbreakAgents as $agent) {
            $name = $agent['user_name'] ?? 'Unknown';
            $minutes = $agent['over_minutes'] ?? 0;

            // Each agent called out 3 times
            $messages[] = "{$name}. {$minutes} minutes overbreak. Warning one.";
            $messages[] = "{$name}. Still on overbreak. Warning two.";
            $messages[] = "{$name}. Final warning. Report to supervisor now.";
        }

        $text = "Overbreak alert. " . implode(" ", $messages);
        return $this->generateSpeech($text);
    }
}
