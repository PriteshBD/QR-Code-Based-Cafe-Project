// Notification System for P&S Cafe Staff Dashboards
// Handles real-time alerts with sound and popup notifications

class CafeNotificationManager {
    constructor(staffRole, checkInterval = 8000) {
        this.staffRole = staffRole;
        this.checkInterval = checkInterval;
        this.lastCheckTime = Math.floor(Date.now() / 1000);
        this.notificationQueue = [];
        this.isPlaying = false;
        this.soundEnabled = localStorage.getItem('cafe_notifications_sound') !== 'false';
        this.popupEnabled = localStorage.getItem('cafe_notifications_popup') !== 'false';
        
        this.initializeAudio();
        this.initializeSettings();
    }

    // Initialize notification sound
    initializeAudio() {
        // Create audio context for notification sound
        this.audioContext = null;
        try {
            const audioContext = window.AudioContext || window.webkitAudioContext;
            if (audioContext) {
                this.audioContext = new audioContext();
            }
        } catch (e) {
            console.log('Web Audio API not supported');
        }
    }

    // Initialize notification settings UI
    initializeSettings() {
        // Check for existing notification settings button
        if (!document.getElementById('notificationSettings')) {
            this.createSettingsButton();
        }
    }

    // Create settings button in top-right corner
    createSettingsButton() {
        const settingsBtn = document.createElement('div');
        settingsBtn.id = 'notificationSettings';
        settingsBtn.innerHTML = `
            <style>
                #notificationSettings {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 1000;
                }
                .notification-settings-btn {
                    background: #667eea;
                    color: white;
                    border: none;
                    padding: 10px 15px;
                    border-radius: 25px;
                    cursor: pointer;
                    font-size: 0.9em;
                    font-weight: bold;
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                    transition: all 0.3s;
                }
                .notification-settings-btn:hover {
                    background: #764ba2;
                    transform: translateY(-2px);
                }
                .notification-badge {
                    display: inline-block;
                    background: #ff4444;
                    color: white;
                    border-radius: 50%;
                    width: 24px;
                    height: 24px;
                    line-height: 24px;
                    text-align: center;
                    font-size: 0.8em;
                    font-weight: bold;
                    margin-left: 5px;
                }
                .settings-modal {
                    display: none;
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    border-radius: 15px;
                    padding: 25px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    z-index: 2000;
                    min-width: 300px;
                }
                .settings-modal.active {
                    display: block;
                }
                .settings-overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 1999;
                }
                .settings-overlay.active {
                    display: block;
                }
                .settings-modal h2 {
                    margin: 0 0 20px 0;
                    color: #333;
                }
                .setting-item {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 12px 0;
                    border-bottom: 1px solid #e0e0e0;
                }
                .setting-item:last-child {
                    border-bottom: none;
                }
                .toggle-switch {
                    position: relative;
                    display: inline-block;
                    width: 50px;
                    height: 24px;
                }
                .toggle-switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .toggle-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .3s;
                    border-radius: 24px;
                }
                .toggle-slider:before {
                    position: absolute;
                    content: "";
                    height: 18px;
                    width: 18px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .3s;
                    border-radius: 50%;
                }
                input:checked + .toggle-slider {
                    background-color: #667eea;
                }
                input:checked + .toggle-slider:before {
                    transform: translateX(26px);
                }
                .close-btn {
                    background: #e0e0e0;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    margin-top: 20px;
                }
            </style>
            <button class="notification-settings-btn" onclick="cafeNotifications.toggleSettings()">
                🔔 Notifications <span class="notification-badge" id="unreadBadge" style="display:none;">0</span>
            </button>
        `;
        document.body.appendChild(settingsBtn);

        // Add modal HTML
        const modal = document.createElement('div');
        modal.id = 'notificationSettingsModal';
        modal.innerHTML = `
            <div class="settings-overlay" id="settingsOverlay"></div>
            <div class="settings-modal" id="settingsModalContent">
                <h2>🔔 Notification Settings</h2>
                <div class="setting-item">
                    <label>Enable Sound Alerts</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="soundToggle" ${this.soundEnabled ? 'checked' : ''} onchange="cafeNotifications.toggleSound()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <label>Enable Popup Notifications</label>
                    <label class="toggle-switch">
                        <input type="checkbox" id="popupToggle" ${this.popupEnabled ? 'checked' : ''} onchange="cafeNotifications.togglePopup()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <label>Check Interval (seconds)</label>
                    <input type="number" id="checkIntervalInput" value="${this.checkInterval / 1000}" min="3" max="60" style="width: 60px; padding: 5px;">
                </div>
                <button class="close-btn" onclick="cafeNotifications.saveSettings()">Save & Close</button>
            </div>
        `;
        document.body.appendChild(modal);

        // Add overlay click handler
        document.getElementById('settingsOverlay').addEventListener('click', () => {
            this.toggleSettings();
        });
    }

    // Play notification sound using Web Audio API
    playNotificationSound() {
        if (!this.soundEnabled || !this.audioContext) return;

        try {
            const context = this.audioContext;
            const oscillator = context.createOscillator();
            const gainNode = context.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(context.destination);

            // Create a pleasant "ding" sound
            oscillator.frequency.value = 800; // Frequency in Hz
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, context.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.5);

            oscillator.start(context.currentTime);
            oscillator.stop(context.currentTime + 0.5);
        } catch (e) {
            console.log('Could not play sound:', e);
        }
    }

    // Show browser notification
    showBrowserNotification(title, message, data = {}) {
        if (!this.popupEnabled) return;

        // First, check if browser supports notifications
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                this.createNotification(title, message, data);
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        this.createNotification(title, message, data);
                    }
                });
            }
        } else {
            // Fallback: create custom toast notification
            this.createToastNotification(title, message, data);
        }
    }

    // Create native browser notification
    createNotification(title, message, data) {
        const notification = new Notification(title, {
            body: message,
            icon: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"%3E%3Ctext x="50" y="70" font-size="70" text-anchor="middle"%3E☕%3C/text%3E%3C/svg%3E',
            tag: 'cafe-order-' + data.order_id,
            requireInteraction: true
        });

        notification.onclick = () => {
            notification.close();
            if (data.order_id) {
                window.focus();
                // Optionally redirect or highlight the order
            }
        };

        setTimeout(() => notification.close(), 8000);
    }

    // Create custom toast notification (fallback)
    createToastNotification(title, message, data) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            z-index: 3000;
            max-width: 350px;
            animation: slideIn 0.3s ease;
        `;

        const titleEl = document.createElement('div');
        titleEl.style.fontWeight = 'bold';
        titleEl.style.fontSize = '1em';
        titleEl.textContent = title;

        const messageEl = document.createElement('div');
        messageEl.style.fontSize = '0.9em';
        messageEl.style.marginTop = '8px';
        messageEl.textContent = message;

        toast.appendChild(titleEl);
        toast.appendChild(messageEl);
        document.body.appendChild(toast);

        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);

        setTimeout(() => toast.remove(), 6000);
    }

    // Start listening for notifications
    start() {
        console.log(`🔔 Notification system started for ${this.staffRole}`);
        this.checkForNotifications();
        setInterval(() => this.checkForNotifications(), this.checkInterval);
    }

    // Check for new notifications
    checkForNotifications() {
        fetch('../api/get_notifications.php?staff_role=' + this.staffRole + '&last_check=' + this.lastCheckTime)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        this.playNotificationSound();
                        this.showBrowserNotification(
                            notification.title,
                            notification.message,
                            { order_id: notification.id }
                        );
                        this.showUnreadBadge(data.notifications.length);
                    });
                    // Update last check time
                    this.lastCheckTime = data.timestamp;
                }
            })
            .catch(error => console.error('Error checking notifications:', error));
    }

    // Show unread notifications badge
    showUnreadBadge(count) {
        const badge = document.getElementById('unreadBadge');
        if (badge && count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        }
    }

    // Toggle settings modal
    toggleSettings() {
        const modal = document.getElementById('settingsModalContent');
        const overlay = document.getElementById('settingsOverlay');
        modal.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Toggle sound setting
    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        localStorage.setItem('cafe_notifications_sound', this.soundEnabled);
        if (this.soundEnabled) {
            this.playNotificationSound(); // Play test sound
        }
    }

    // Toggle popup setting
    togglePopup() {
        this.popupEnabled = !this.popupEnabled;
        localStorage.setItem('cafe_notifications_popup', this.popupEnabled);
    }

    // Save settings
    saveSettings() {
        const interval = parseInt(document.getElementById('checkIntervalInput').value) * 1000;
        if (interval >= 3000 && interval <= 60000) {
            this.checkInterval = interval;
            localStorage.setItem('cafe_notifications_interval', interval);
        }
        this.toggleSettings();
    }
}

// Global notification manager instance
let cafeNotifications = null;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Get staff role from data attribute or URL
    let staffRole = document.body.getAttribute('data-staff-role') || 
                    new URLSearchParams(window.location.search).get('role') ||
                    'staff';
    
    cafeNotifications = new CafeNotificationManager(staffRole);
    cafeNotifications.start();
    
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});
