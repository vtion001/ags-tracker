import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Only initialize Echo if VITE_REVERB_HOST is set AND not localhost (production)
const reverbHost = import.meta.env.VITE_REVERB_HOST;
const isLocalhost = reverbHost && (reverbHost === 'localhost' || reverbHost === '127.0.0.1' || reverbHost.includes('localhost'));

if (reverbHost && !isLocalhost) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: reverbHost,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    // Mock Echo for environments without broadcasting or localhost development
    window.Echo = {
        channel: () => ({ listen: () => {} }),
        private: () => ({ listen: () => {} }),
    };
}
