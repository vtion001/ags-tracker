<?php

return [
    'api_key' => env('ELEVENLABS_API_KEY'),
    'voice_id' => env('ELEVENLABS_VOICE_ID', 'CwhRBWXzGAHq8TQ4Fs17'),
    'model' => env('ELEVENLABS_MODEL', 'eleven_flash_v2_5'),
    'proximity_filter' => [
        'frontend_micro_seconds' => env('ELEVENLABS_PROXIMITY_FILTER', 1000000),
    ],
];
