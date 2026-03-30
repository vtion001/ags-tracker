<?php

namespace App\Http\Controllers;

use App\Services\ElevenLabsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AlertController extends Controller
{
    public function __construct(
        protected ElevenLabsService $elevenLabs
    ) {}

    public function overbreakAlert(Request $request): Response
    {
        if (!$this->elevenLabs->isConfigured()) {
            return response('Voice alerts not configured', 503);
        }

        // Support both GET and POST
        $agentName = $request->input('agent_name') ?? $request->query('agent_name');
        $overMinutes = $request->input('over_minutes') ?? $request->query('over_minutes');

        if (!$agentName || !$overMinutes) {
            return response('Missing parameters', 400);
        }

        $audio = $this->elevenLabs->generateOverbreakAlert($agentName, (int) $overMinutes);

        if ($audio === null) {
            return response('Audio generation failed', 503);
        }

        return response($audio, 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Content-Disposition', 'inline');
    }

    public function batchAlert(Request $request): Response
    {
        if (!$this->elevenLabs->isConfigured()) {
            return response('Voice alerts not configured', 503);
        }

        $agents = $request->input('agents', []);

        if (empty($agents)) {
            return response('No agents', 400);
        }

        $audio = $this->elevenLabs->generateOverbreakBatchAlert($agents);

        if ($audio === null) {
            return response('Audio generation failed', 503);
        }

        return response($audio, 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Content-Disposition', 'inline');
    }

    /**
     * Test endpoint - generates a generic voice alert without requiring parameters.
     * Use this to test ElevenLabs integration.
     */
    public function testAlert(): Response
    {
        if (!$this->elevenLabs->isConfigured()) {
            return response('Voice alerts not configured. Set ELEVENLABS_API_KEY and ELEVENLABS_VOICE_ID in environment.', 503);
        }

        $audio = $this->elevenLabs->generateSpeech(
            "This is an overbreak notification. Agent Maria Santos has exceeded the allowable break duration by 20 minutes. Kindly address this matter promptly."
        );

        if ($audio === null) {
            return response('Audio generation failed', 503);
        }

        return response($audio, 200)
            ->header('Content-Type', 'audio/mpeg')
            ->header('Content-Disposition', 'inline');
    }
}
