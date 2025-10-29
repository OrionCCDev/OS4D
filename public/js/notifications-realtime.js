/**
 * Real-time Notification System using Laravel Echo
 *
 * This script handles real-time notification updates via WebSockets
 * Replaces the old AJAX polling system with instant push notifications
 */

(function() {
    'use strict';

    // Configuration
    const config = {
        maxReconnectAttempts: 5,
        ajaxFallbackInterval: 30000, // 30 seconds
        debug: true // Set to false in production
    };

    // State management
    let isConnected = false;
    let reconnectAttempts = 0;
    let ajaxFallbackTimer = null;
    let notificationChannel = null;

    /**
     * Initialize real-time notifications
     */
    function initializeNotifications() {
        // Check if Echo is available
        if (typeof Echo === 'undefined') {
            console.error('[Notifications] Laravel Echo is not loaded');
            startAjaxFallback();
            return;
        }

        // Get current user ID from meta tag
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

        if (!userId) {
            console.warn('[Notifications] User ID not found, real-time notifications disabled');
            startAjaxFallback();
            return;
        }

        log('Initializing real-time notifications for user:', userId);

        // Subscribe to private user channel
        subscribeToUserChannel(userId);

        // Handle connection events
        setupConnectionHandlers();

        // Initial fetch of notification counts
        fetchNotificationCounts();

        // Request browser notification permission
        requestNotificationPermission();
    }

    /**
     * Subscribe to the user's private notification channel
     */
    function subscribeToUserChannel(userId) {
        try {
            notificationChannel = Echo.private(`user.${userId}`);

            // Listen for successful subscription
            notificationChannel.subscribed(() => {
                log('✓ Subscribed to notification channel');
                isConnected = true;
                reconnectAttempts = 0;
                updateConnectionStatus(true);
                stopAjaxFallback();
            });

            // Listen for errors
            notificationChannel.error((error) => {
                console.error('[Notifications] Channel error:', error);
                isConnected = false;
                updateConnectionStatus(false);
                handleConnectionError();
            });

            // Listen for new notifications
            notificationChannel.listen('.notification.new', handleNewNotification);

            // Listen for notification read events
            notificationChannel.listen('.notification.read', handleNotificationRead);

            // Listen for count updates
            notificationChannel.listen('.notification.count.updated', handleCountUpdated);

        } catch (error) {
            console.error('[Notifications] Failed to subscribe to channel:', error);
            startAjaxFallback();
        }
    }

    /**
     * Setup connection event handlers
     */
    function setupConnectionHandlers() {
        if (!Echo.connector || !Echo.connector.pusher) return;

        const pusher = Echo.connector.pusher;

        pusher.connection.bind('connected', () => {
            log('✓ WebSocket connected');
            isConnected = true;
            updateConnectionStatus(true);
        });

        pusher.connection.bind('disconnected', () => {
            log('✗ WebSocket disconnected');
            isConnected = false;
            updateConnectionStatus(false);
        });

        pusher.connection.bind('error', (error) => {
            console.error('[Notifications] WebSocket error:', error);
            handleConnectionError();
        });
    }

    /**
     * Handle new notification received
     */
    function handleNewNotification(data) {
        log('New notification received:', data);

        // Update counts immediately
        fetchNotificationCounts();

        // Play notification sound
        playNotificationSound(data.category);

        // Show browser notification
        showBrowserNotification(data);

        // Show toast notification
        showToastNotification(data);

        // Add to dropdown
        prependNotificationToDropdown(data);
    }

    /**
     * Handle notification marked as read
     */
    function handleNotificationRead(data) {
        log('Notification marked as read:', data.notification_id);

        // Update counts
        if (data.counts) {
            updateNotificationCounts(data.counts);
        }

        // Remove from UI
        removeNotificationFromDropdown(data.notification_id);
    }

    /**
     * Handle notification count update
     */
    function handleCountUpdated(data) {
        log('Notification counts updated:', data.counts);

        if (data.counts) {
            updateNotificationCounts(data.counts);
        }
    }

    /**
     * Update notification counts in the UI
     */
    function updateNotificationCounts(counts) {
        // Update email badge
        const emailBadge = document.getElementById('email-notifications-count');
        if (emailBadge) {
            emailBadge.textContent = counts.email || 0;
            emailBadge.style.display = counts.email > 0 ? 'inline-block' : 'none';
        }

        // Update task badge
        const taskBadge = document.getElementById('task-notifications-count');
        if (taskBadge) {
            taskBadge.textContent = counts.task || 0;
            taskBadge.style.display = counts.task > 0 ? 'inline-block' : 'none';
        }

        // Update total badge
        const totalBadge = document.getElementById('notification-count');
        if (totalBadge) {
            totalBadge.textContent = counts.total || 0;
            totalBadge.style.display = counts.total > 0 ? 'inline-block' : 'none';
        }

        log('UI updated with counts:', counts);
    }

    /**
     * Play notification sound based on category
     */
    function playNotificationSound(category) {
        let audioElement;

        if (category === 'email') {
            audioElement = document.getElementById('notification-sound');
            if (typeof playNotificationSound === 'function') {
                playNotificationSound(); // Call existing function if available
                return;
            }
        } else if (category === 'task') {
            audioElement = document.getElementById('task-notification-sound');
            if (typeof playTaskNotificationSound === 'function') {
                playTaskNotificationSound(); // Call existing function if available
                return;
            }
        }

        // Fallback to direct audio play
        if (audioElement) {
            audioElement.play().catch(e => log('Audio play failed:', e));
        }
    }

    /**
     * Show browser notification
     */
    function showBrowserNotification(data) {
        if (Notification.permission !== 'granted') return;

        const notification = new Notification(data.title, {
            body: data.message,
            icon: '/favicon.ico',
            tag: `notification-${data.id}`,
            requireInteraction: false,
        });

        // Handle notification click
        notification.onclick = function() {
            if (data.action_url) {
                window.location.href = data.action_url;
            }
            notification.close();
        };

        // Auto close after 5 seconds
        setTimeout(() => notification.close(), 5000);
    }

    /**
     * Show toast notification (Bootstrap Toast)
     */
    function showToastNotification(data) {
        // Check if Bootstrap is available
        if (typeof bootstrap === 'undefined') {
            log('Bootstrap not available for toast notifications');
            return;
        }

        const toastHtml = `
            <div class="toast notification-toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class='${data.icon} me-2' style="color: ${data.color}"></i>
                    <strong class="me-auto">${escapeHtml(data.title)}</strong>
                    <small>${data.time_ago || 'Just now'}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${escapeHtml(data.message)}
                </div>
            </div>
        `;

        const container = getOrCreateToastContainer();
        container.insertAdjacentHTML('beforeend', toastHtml);

        const toastElement = container.lastElementChild;
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();

        // Remove from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Get or create toast container
     */
    function getOrCreateToastContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Prepend notification to dropdown
     */
    function prependNotificationToDropdown(data) {
        const dropdown = document.querySelector('.notification-dropdown-menu');
        if (!dropdown) return;

        const notificationHtml = `
            <a href="${data.action_url || '#'}"
               class="dropdown-item notification-item unread"
               data-notification-id="${data.id}">
                <div class="d-flex align-items-start">
                    <i class='${data.icon} me-2' style="color: ${data.color}"></i>
                    <div class="flex-grow-1">
                        <div class="notification-title fw-bold">${escapeHtml(data.title)}</div>
                        <div class="notification-message text-muted small">${escapeHtml(data.message)}</div>
                        <div class="notification-time text-muted small">${data.time_ago || 'Just now'}</div>
                    </div>
                </div>
            </a>
        `;

        dropdown.insertAdjacentHTML('afterbegin', notificationHtml);

        // Keep only last 10 notifications
        const items = dropdown.querySelectorAll('.notification-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }

    /**
     * Remove notification from dropdown
     */
    function removeNotificationFromDropdown(notificationId) {
        const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (item) {
            item.classList.remove('unread');
            item.classList.add('read');
        }
    }

    /**
     * Fetch notification counts via AJAX (fallback or initial load)
     */
    async function fetchNotificationCounts() {
        try {
            const response = await fetch('/notifications/unread-count');
            const data = await response.json();

            if (data.success && data.counts) {
                updateNotificationCounts(data.counts);
            }
        } catch (error) {
            console.error('[Notifications] Failed to fetch counts:', error);
        }
    }

    /**
     * Start AJAX polling fallback
     */
    function startAjaxFallback() {
        if (ajaxFallbackTimer) return; // Already running

        log('Starting AJAX polling fallback');
        ajaxFallbackTimer = setInterval(fetchNotificationCounts, config.ajaxFallbackInterval);
        fetchNotificationCounts(); // Initial fetch
    }

    /**
     * Stop AJAX polling fallback
     */
    function stopAjaxFallback() {
        if (ajaxFallbackTimer) {
            clearInterval(ajaxFallbackTimer);
            ajaxFallbackTimer = null;
            log('Stopped AJAX polling fallback');
        }
    }

    /**
     * Handle connection error
     */
    function handleConnectionError() {
        reconnectAttempts++;

        if (reconnectAttempts >= config.maxReconnectAttempts) {
            console.warn('[Notifications] Max reconnection attempts reached, falling back to AJAX polling');
            startAjaxFallback();
        }
    }

    /**
     * Update connection status indicator
     */
    function updateConnectionStatus(connected) {
        const indicator = document.getElementById('notification-status-indicator');
        if (indicator) {
            indicator.className = connected ? 'status-connected' : 'status-disconnected';
            indicator.title = connected ? 'Real-time notifications active' : 'Reconnecting...';
        }
    }

    /**
     * Request browser notification permission
     */
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                log('Notification permission:', permission);
            });
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Log messages (only if debug is enabled)
     */
    function log(...args) {
        if (config.debug) {
            console.log('[Notifications]', ...args);
        }
    }

    /**
     * Expose debug functions for testing
     */
    window.notificationDebug = {
        getConnectionStatus: () => isConnected,
        getReconnectAttempts: () => reconnectAttempts,
        getChannel: () => notificationChannel,
        testNotification: () => {
            handleNewNotification({
                id: 'test-' + Date.now(),
                category: 'task',
                type: 'test',
                title: 'Test Notification',
                message: 'This is a test notification from the debug console',
                icon: 'bx bx-bell',
                color: '#007bff',
                time_ago: 'Just now'
            });
        },
        forceAjaxFallback: startAjaxFallback,
        stopAjaxFallback: stopAjaxFallback,
        fetchCounts: fetchNotificationCounts
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeNotifications);
    } else {
        initializeNotifications();
    }

})();
