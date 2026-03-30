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

    public function generateOverbreakAlert(string $agentName, int $overMinutes, int $warningNumber = 1): ?string
    {
        $index = max(0, min($warningNumber - 1, 2)); // 1-3 -> 0-2
        $script = str_replace('{name}', $agentName, $this->alertScripts[$index]);
        return $this->generateSpeech($script);
    }

    public function generateOverbreakBatchAlert(array $overbreakAgents): ?string
    {
        if (empty($overbreakAgents)) {
            return null;
        }

        $names = implode(', ', array_column($overbreakAgents, 'user_name'));
        $text = "Alert. The following agents are on overbreak: {$names}. Please take immediate action.";
        return $this->generateSpeech($text);
    }
}
