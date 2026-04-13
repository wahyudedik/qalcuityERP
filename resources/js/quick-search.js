/**
 * Quick Search - Command Palette (Ctrl+K)
 * VS Code/Raycast style universal search
 */

import logger from './logger';

export class QuickSearch {
    constructor() {
        this.isOpen = false;
        this.query = '';
        this.results = [];
        this.selectedIndex = 0;
        this.recentSearches = JSON.parse(localStorage.getItem('recent_searches') || '[]');
        this.savedSearches = [];
        this.suggestions = [];
        this.debounceTimer = null;
        this.isLoading = false;
        this.init();
    }

    init() {
        this.createModal();
        this.setupEventListeners();

        logger.debug('[QuickSearch] Initialized');
    }

    createModal() {
        const modal = document.createElement('div');
        modal.id = 'quick-search-modal';
        modal.innerHTML = `
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9998]" id="quick-search-backdrop"></div>
            
            <!-- Modal -->
            <div class="fixed top-[10vh] left-1/2 -translate-x-1/2 w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl z-[9999] overflow-hidden" role="dialog" aria-modal="true" aria-labelledby="quick-search-title">
                <!-- Search Input -->
                <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-search text-gray-400 mr-3 text-lg"></i>
                    <input 
                        type="text" 
                        id="quick-search-input"
                        class="flex-1 bg-transparent border-none outline-none text-lg text-gray-900 dark:text-white placeholder-gray-400"
                        placeholder="Search or jump to..."
                        autocomplete="off"
                        aria-label="Quick search"
                    >
                    <kbd class="hidden sm:inline-block px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600">ESC</kbd>
                </div>
                
                <!-- Results Container -->
                <div id="quick-search-results" class="max-h-[60vh] overflow-y-auto">
                    <!-- Results will be injected here -->
                </div>
                
                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span><kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">↑↓</kbd> Navigate</span>
                        <span><kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">↵</kbd> Select</span>
                        <span><kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">ESC</kbd> Close</span>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Setup event listeners
        document.getElementById('quick-search-backdrop')?.addEventListener('click', () => this.close());
        document.getElementById('quick-search-input')?.addEventListener('input', (e) => this.handleInput(e));
        document.getElementById('quick-search-input')?.addEventListener('keydown', (e) => this.handleKeydown(e));
    }

    setupEventListeners() {
        // Listen for open event from keyboard shortcuts
        window.addEventListener('open-quick-search', () => {
            this.open();
        });

        // Global Ctrl+K listener (fallback)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    open() {
        this.isOpen = true;
        const modal = document.getElementById('quick-search-modal');
        if (modal) {
            modal.classList.remove('hidden');
            const input = document.getElementById('quick-search-input');
            input?.focus();

            // Load saved searches and show recent searches or placeholder
            this.loadSavedSearches();
            this.showRecentSearches();
        }
    }

    close() {
        this.isOpen = false;
        const modal = document.getElementById('quick-search-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
        this.query = '';
        this.results = [];
        this.selectedIndex = 0;
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    handleInput(e) {
        this.query = e.target.value;

        // Clear previous debounce timer
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        // Debounce search
        this.debounceTimer = setTimeout(() => {
            if (this.query.length >= 2) {
                this.fetchSuggestions();
                this.search();
            } else if (this.query.length === 0) {
                this.showRecentSearches();
            }
        }, 200);
    }

    handleKeydown(e) {
        if (e.key === 'Escape') {
            this.close();
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
            this.updateSelection();
            return;
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
            this.updateSelection();
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            this.selectResult();
            return;
        }

        // Ctrl+S to save search
        if ((e.ctrlKey || e.metaKey) && e.key === 's' && this.query.length >= 2) {
            e.preventDefault();
            this.saveCurrentSearch();
            return;
        }
    }

    async search() {
        if (this.query.length < 2) return;

        const input = document.getElementById('quick-search-input');
        input && (input.disabled = true);
        this.isLoading = true;

        try {
            const response = await fetch(`/api/quick-search?q=${encodeURIComponent(this.query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (response.ok) {
                const data = await response.json();
                // Prepend suggestions to results
                this.results = [...this.suggestions, ...(data.results || [])];
                this.selectedIndex = 0;
                this.renderResults();

                // Save to recent searches
                this.saveRecentSearch(this.query);
            }
        } catch (error) {
            logger.error('[QuickSearch] Search error', error);
        } finally {
            input && (input.disabled = false);
            this.isLoading = false;
        }
    }

    showRecentSearches() {
        const container = document.getElementById('quick-search-results');
        if (!container) return;

        if (this.recentSearches.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-search text-4xl mb-3 opacity-30"></i>
                    <p>Start typing to search...</p>
                </div>
            `;
            return;
        }

        const html = `
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Recent Searches</h3>
            </div>
            ${this.recentSearches.slice(0, 5).map(search => `
                <button 
                    class="w-full flex items-center p-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-left"
                    onclick="window.quickSearch.setInput('${search}')"
                >
                    <i class="fas fa-history text-gray-400 mr-3"></i>
                    <span class="text-gray-700 dark:text-gray-300">${search}</span>
                </button>
            `).join('')}
        `;

        container.innerHTML = html;
        this.results = [];
    }

    renderResults() {
        const container = document.getElementById('quick-search-results');
        if (!container) return;

        if (this.results.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-search-minus text-4xl mb-3 opacity-30"></i>
                    <p>No results found for "${this.query}"</p>
                    <p class="text-xs mt-2">Press <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded">Ctrl+S</kbd> to save this search</p>
                </div>
            `;
            return;
        }

        const html = this.results.map((result, index) => {
            // Determine background color based on type
            let bgColor = '';
            if (result.type === 'saved_search') {
                bgColor = index === this.selectedIndex ? 'bg-purple-50 dark:bg-purple-900/20' : '';
            } else if (result.type === 'recent') {
                bgColor = index === this.selectedIndex ? 'bg-blue-50 dark:bg-blue-900/20' : '';
            } else {
                bgColor = index === this.selectedIndex ? 'bg-blue-50 dark:bg-blue-900/20' : '';
            }

            return `
                <a 
                    href="${result.url}" 
                    class="flex items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700/50 ${bgColor}"
                    data-index="${index}"
                    ${result.action ? `onclick="window.quickSearch.handleAction('${result.action}', ${JSON.stringify(result).replace(/"/g, '&quot;')}); return false;"` : ''}
                >
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg ${result.type === 'saved_search' ? 'bg-purple-100 dark:bg-purple-900/30' : result.type === 'recent' ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-gray-100 dark:bg-gray-700'} mr-3">
                        <i class="${result.icon || 'fas fa-file'} ${result.type === 'saved_search' ? 'text-purple-600 dark:text-purple-400' : result.type === 'recent' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400'}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">${result.title}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">${result.subtitle || ''}</div>
                    </div>
                    ${result.badge ? `<span class="ml-3 px-2 py-1 text-xs font-medium ${result.type === 'saved_search' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : result.type === 'recent' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'} rounded">${result.badge}</span>` : ''}
                </a>
            `;
        }).join('');

        container.innerHTML = html;
    }

    updateSelection() {
        const links = document.querySelectorAll('#quick-search-results a[data-index]');
        links.forEach((link, index) => {
            if (index === this.selectedIndex) {
                link.classList.add('bg-blue-50', 'dark:bg-blue-900/20');
                link.scrollIntoView({ block: 'nearest' });
            } else {
                link.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
            }
        });
    }

    selectResult() {
        if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
            const result = this.results[this.selectedIndex];
            if (result.action) {
                this.handleAction(result.action, result);
            } else if (result.url) {
                window.location.href = result.url;
            }
        }
    }

    handleAction(action, result = {}) {
        switch (action) {
            case 'toggle-theme':
                window.dispatchEvent(new CustomEvent('toggle-theme'));
                this.close();
                break;
            case 'execute-saved':
                if (result.saved_search_id) {
                    this.executeSavedSearch(result.saved_search_id);
                }
                break;
            case 'set-query':
                if (result.query) {
                    this.setInput(result.query);
                }
                break;
            default:
                logger.warn(`[QuickSearch] Unknown action: ${action}`);
        }
    }

    setInput(value) {
        const input = document.getElementById('quick-search-input');
        if (input) {
            input.value = value;
            this.query = value;
            this.search();
        }
    }

    saveRecentSearch(query) {
        // Remove if already exists
        this.recentSearches = this.recentSearches.filter(s => s !== query);
        // Add to beginning
        this.recentSearches.unshift(query);
        // Keep only last 10
        this.recentSearches = this.recentSearches.slice(0, 10);
        // Save
        localStorage.setItem('recent_searches', JSON.stringify(this.recentSearches));
    }

    async loadSavedSearches() {
        try {
            const response = await fetch('/api/saved-searches?sort=recent', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.savedSearches = data.data || [];
            }
        } catch (error) {
            logger.error('[QuickSearch] Failed to load saved searches', error);
        }
    }

    async fetchSuggestions() {
        if (this.query.length < 2) return;

        try {
            const response = await fetch(`/api/saved-searches/suggestions/search?q=${encodeURIComponent(this.query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.suggestions = data.suggestions || [];
            }
        } catch (error) {
            logger.error('[QuickSearch] Failed to fetch suggestions', error);
        }
    }

    async saveCurrentSearch() {
        if (this.query.length < 2) return;

        const name = prompt('Nama pencarian yang disimpan:', this.query);
        if (!name) return;

        try {
            const response = await fetch('/api/saved-searches', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({
                    name: name,
                    query: this.query,
                    type: 'all',
                })
            });

            if (response.ok) {
                const data = await response.json();
                logger.info('[QuickSearch] Search saved:', data.message);
                this.loadSavedSearches();
            }
        } catch (error) {
            logger.error('[QuickSearch] Failed to save search', error);
        }
    }

    async executeSavedSearch(savedSearchId) {
        try {
            const response = await fetch(`/api/saved-searches/${savedSearchId}/execute`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.results = data.results || [];
                this.selectedIndex = 0;
                this.renderResults();
            }
        } catch (error) {
            logger.error('[QuickSearch] Failed to execute saved search', error);
        }
    }
}

// Initialize quick search
let quickSearch = null;

export function initQuickSearch() {
    if (!quickSearch) {
        quickSearch = new QuickSearch();
        window.quickSearch = quickSearch; // Make globally accessible
    }
    return quickSearch;
}

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuickSearch);
} else {
    initQuickSearch();
}

export default QuickSearch;
