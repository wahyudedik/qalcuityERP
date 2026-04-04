/**
 * Chat Module - Lazy Loaded Chunk
 * Optimized for on-demand loading
 */

// Import only what's needed
import { marked } from 'marked';
import DOMPurify from 'dompurify';

/**
 * Chat Manager Class
 * Handles all chat functionality in a lazy-loaded chunk
 */
class ChatManager {
    constructor(options = {}) {
        this.options = {
            maxMessages: 100,
            typingDelay: 300,
            autoScroll: true,
            ...options
        };

        this.messages = [];
        this.isTyping = false;
        this.init();
    }

    init() {
        console.log('[Chat] Initialized');
        this.bindEvents();
    }

    bindEvents() {
        // Event binding logic here
        document.addEventListener('chat:send', (e) => {
            this.sendMessage(e.detail.message);
        });
    }

    sendMessage(message) {
        // Sanitize message
        const cleanMessage = DOMPurify.sanitize(message);

        // Add to messages
        this.messages.push({
            text: cleanMessage,
            timestamp: new Date(),
            from: 'user'
        });

        // Render
        this.render();
    }

    render() {
        // Render messages to DOM
        const container = document.querySelector('.chat-messages');
        if (!container) return;

        container.innerHTML = this.messages.map(msg => `
            <div class="message ${msg.from}">
                <span class="text">${this.parseMarkdown(msg.text)}</span>
                <span class="time">${this.formatTime(msg.timestamp)}</span>
            </div>
        `).join('');

        // Auto scroll
        if (this.options.autoScroll) {
            container.scrollTop = container.scrollHeight;
        }
    }

    parseMarkdown(text) {
        return marked.parse(text);
    }

    formatTime(date) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
}

// Export factory function
export function initChat(options) {
    return new ChatManager(options);
}

// Auto-initialize if element exists
if (document.querySelector('.chat-container')) {
    window.chatManager = initChat();
}

console.log('[Chat] Module loaded');
