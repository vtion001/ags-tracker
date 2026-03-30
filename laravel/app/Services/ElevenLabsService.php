<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ElevenLabsService
{
    private string $apiKey;
    private string $voiceId;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('elevenlabs.api_key');
        $this->voiceId = config('elevenlabs.voice_id');
        $this->model = config('elevenlabs.model');
    }

    public function generateSpeech(string $text): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('ElevenLabs API key not configured');
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
