/**
 * Laravel Echo Configuration
 *
 * This file configures Laravel Echo for real-time WebSocket communication
 * using Laravel WebSockets (Pusher protocol compatible)
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally
window.Pusher = Pusher;

// Configure Laravel Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'your-app-key',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
    encrypted: true,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json',
        }
    }
});

// Log connection status
console.log('[Laravel Echo] Initialized with host:', window.location.hostname);

// Handle connection events
if (window.Echo.connector && window.Echo.connector.pusher) {
    const pusher = window.Echo.connector.pusher;

    pusher.connection.bind('connected', () => {
        console.log('[Laravel Echo] ✓ Connected to WebSocket server');
    });

    pusher.connection.bind('disconnected', () => {
        console.warn('[Laravel Echo] ✗ Disconnected from WebSocket server');
    });

    pusher.connection.bind('error', (error) => {
        console.error('[Laravel Echo] Connection error:', error);
    });

    pusher.connection.bind('state_change', (states) => {
        console.log('[Laravel Echo] State changed from', states.previous, 'to', states.current);
    });
}

export default window.Echo;
