/**
 * HelpSystem - Contextual Help System for ERP
 *
 * TASK-015: Implements comprehensive contextual help with:
 * - Help modal with rich content
 * - Tooltips on hover
 * - Video tutorials
 * - Step-by-step guides
 * - Search functionality
 * - Multi-language support (Indonesian)
 *
 * @version 1.0.0
 */

import logger from './logger';

class HelpSystem {
    constructor() {
        this.topics = new Map();
        this.currentTopic = null;
        this.modalVisible = false;
        this.searchIndex = [];

        // Default help content
        this.loadDefaultTopics();
    }

    /**
     * Load default help topics
     */
    loadDefaultTopics() {
        const defaultTopics = {
            // Sales Module
            'customer-selection': {
                title: 'Cara Memilih Customer',
                module: 'sales',
                page: 'invoices.create',
                field: 'customer_id',
                content: 'Pilih customer dari daftar yang tersedia. Anda bisa mencari customer berdasarkan nama, email, atau nomor telepon.',
                tips: [
                    'Gunakan kolom search untuk mencari customer dengan cepat',
                    'Klik "Tambah Customer Baru" jika customer belum ada di daftar',
                    'Customer yang sudah dinonaktifkan tidak akan muncul di daftar',
                    'Anda bisa melihat detail customer dengan klik ikon mata'
                ],
                video: '/help/videos/customer-selection.mp4',
                documentation: '/docs/sales/customers',
                order: 1
            },

            'sales-order-link': {
                title: 'Link Invoice ke Sales Order',
                module: 'sales',
                page: 'invoices.create',
                field: 'sales_order_id',
                content: 'Anda bisa membuat invoice dari Sales Order yang sudah ada. Ini memastikan data konsisten antara pesanan dan tagihan.',
                tips: [
                    'Pilih Sales Order yang sudah dikonfirmasi untuk diinvoice',
                    'Total invoice akan otomatis terisi dari Sales Order',
                    'Satu Sales Order bisa dibuatkan invoice bertahap (partial)',
                    'Sales Order yang sudah diinvoice penuh tidak akan muncul di daftar',
                    'Jika tidak pilih Sales Order, Anda bisa isi manual total invoice'
                ],
                documentation: '/docs/sales/invoices',
                order: 2
            },

            'invoice-total': {
                title: 'Mengisi Total Tagihan Invoice',
                module: 'sales',
                page: 'invoices.create',
                field: 'total_amount',
                content: 'Total tagihan adalah jumlah yang harus dibayar customer. Jika invoice dibuat dari Sales Order, total akan otomatis terisi.',
                tips: [
                    'Jika pilih Sales Order, total otomatis terisi',
                    'Untuk invoice manual, masukkan total sesuai kesepakatan',
                    'Total sudah termasuk PPN jika ada',
                    'Pastikan total benar sebelum menyimpan invoice',
                    'Invoice yang sudah diposting tidak bisa diubah totalnya'
                ],
                order: 3
            },

            'due-date-selection': {
                title: 'Menentukan Tanggal Jatuh Tempo',
                module: 'sales',
                page: 'invoices.create',
                field: 'due_date',
                content: 'Tanggal jatuh tempo adalah batas waktu pembayaran invoice. Default 14 hari dari hari ini.',
                tips: [
                    'Default jatuh tempo: 14 hari dari tanggal invoice',
                    'Sesuaikan dengan term of payment (TOP) yang disepakati',
                    'Invoice yang melewati jatuh tempo akan berstatus Overdue',
                    'Customer bisa dikenakan denda keterlambatan jika ada kesepakatan',
                    'Anda bisa kirim reminder otomatis sebelum jatuh tempo'
                ],
                order: 4
            },

            'invoice-status': {
                title: 'Status Invoice',
                module: 'sales',
                page: 'invoices.index',
                content: 'Invoice memiliki beberapa status yang menunjukkan tahap pembayaran:',
                tips: [
                    'Unpaid: Invoice belum dibayar',
                    'Partial: Invoice sudah dibayar sebagian',
                    'Paid: Invoice sudah lunas',
                    'Overdue: Invoice sudah melewati jatuh tempo',
                    'Cancelled: Invoice dibatalkan'
                ],
                order: 2
            },

            'product-pricing': {
                title: 'Cara Menentukan Harga Produk',
                module: 'sales',
                page: 'products.create',
                field: 'price_sell',
                content: 'Anda bisa menentukan harga jual produk dengan beberapa cara:',
                tips: [
                    'Harga Manual: Masukkan harga secara langsung',
                    'Markup dari Harga Beli: Sistem otomatis menghitung dari harga beli + markup',
                    'Harga Bertingkat: Set harga berbeda untuk quantity berbeda',
                    'Harga Khusus Customer: Set harga khusus untuk customer tertentu'
                ],
                order: 3
            },

            // Inventory Module
            'stock-management': {
                title: 'Manajemen Stok',
                module: 'inventory',
                page: 'products.index',
                content: 'Kelola stok produk dengan fitur berikut:',
                tips: [
                    'Stok minimum: Set batas minimum untuk alert restock',
                    'Multi-gudang: Kelola stok di beberapa gudang',
                    'Batch tracking: Lacak produk berdasarkan batch',
                    'Expiry date: Kelola produk yang memiliki tanggal kadaluarsa',
                    'Stock opname: Lakukan stock opname secara berkala'
                ],
                order: 1
            },

            // HRM Module
            'employee-status': {
                title: 'Status Karyawan',
                module: 'hrm',
                page: 'employees.index',
                content: 'Status karyawan menunjukkan keadaan kepegawaian:',
                tips: [
                    'Active: Karyawan masih aktif bekerja',
                    'Inactive: Karyawan tidak aktif (cuti panjang, dll)',
                    'Terminated: Karyawan sudah tidak bekerja',
                    'Probation: Karyawan masih masa percobaan',
                    'Contract: Karyawan kontrak'
                ],
                order: 1
            },

            'payroll-processing': {
                title: 'Proses Payroll',
                module: 'hrm',
                page: 'payroll.process',
                content: 'Proses payroll bulanan untuk menghitung gaji karyawan:',
                tips: [
                    'Pastikan semua attendance sudah terinput',
                    'Cek overtime yang belum diproses',
                    'Review potongan dan tunjangan',
                    'Generate slip gaji otomatis',
                    'Export ke format yang dibutuhkan'
                ],
                order: 2
            },

            // Finance Module
            'journal-entry': {
                title: 'Cara Membuat Jurnal',
                module: 'finance',
                page: 'journals.create',
                content: 'Jurnal akuntansi mencatat transaksi keuangan:',
                tips: [
                    'Pastikan debit = credit (balance)',
                    'Pilih akun yang sesuai dengan transaksi',
                    'Isi deskripsi yang jelas untuk memudahkan tracking',
                    'Attach dokumen pendukung jika ada',
                    'Review sebelum posting'
                ],
                order: 1
            },

            // Manufacturing Module
            'bom-creation': {
                title: 'Membuat Bill of Materials (BOM)',
                module: 'manufacturing',
                page: 'boms.create',
                content: 'BOM mendefinisikan komponen yang dibutuhkan untuk membuat produk:',
                tips: [
                    'Pilih produk jadi terlebih dahulu',
                    'Tambahkan semua komponen dengan quantity yang tepat',
                    'Set loss percentage jika ada waste',
                    'Review total cost sebelum menyimpan',
                    'BOM bisa memiliki versi untuk tracking perubahan'
                ],
                order: 1
            }
        };

        // Load topics
        Object.entries(defaultTopics).forEach(([key, topic]) => {
            this.registerTopic(key, topic);
        });

        logger.info(`HelpSystem: ${this.topics.size} topics loaded`);
    }

    /**
     * Register a help topic
     *
     * @param {string} key - Unique topic key
     * @param {Object} topic - Topic data
     */
    registerTopic(key, topic) {
        this.topics.set(key, {
            key,
            title: topic.title,
            module: topic.module || 'general',
            page: topic.page || '',
            field: topic.field || '',
            content: topic.content || '',
            tips: topic.tips || [],
            video: topic.video || null,
            image: topic.image || null,
            documentation: topic.documentation || null,
            order: topic.order || 0,
            active: topic.active !== false
        });

        // Add to search index
        this.searchIndex.push({
            key,
            title: topic.title,
            content: topic.content,
            module: topic.module,
            searchable: `${topic.title} ${topic.content} ${topic.tips?.join(' ') || ''}`.toLowerCase()
        });
    }

    /**
     * Show help for a specific topic
     *
     * @param {string} topicKey - Topic key to show
     */
    showHelp(topicKey) {
        const topic = this.topics.get(topicKey);

        if (!topic) {
            logger.warn(`Help topic not found: ${topicKey}`);
            this.showGenericHelp();
            return;
        }

        this.currentTopic = topic;
        this.renderModal(topic);
        this.modalVisible = true;
    }

    /**
     * Show help modal
     *
     * @param {Object} topic - Topic to display
     */
    renderModal(topic) {
        // Remove existing modal if any
        this.hideHelp();

        // Create modal
        const modal = document.createElement('div');
        modal.id = 'help-modal';
        modal.className = 'fixed inset-0 z-50 overflow-y-auto';
        modal.setAttribute('aria-labelledby', 'help-modal-title');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');

        modal.innerHTML = `
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                 id="help-backdrop"></div>

            <!-- Modal -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">

                    <!-- Header -->
                    <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="help-modal-title">
                                        ${this.escapeHtml(topic.title)}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        ${this.escapeHtml(topic.module)} • ${this.escapeHtml(topic.page)}
                                    </p>
                                </div>
                            </div>
                            <button type="button"
                                    id="help-close"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-4 space-y-6">

                        <!-- Main Content -->
                        <div>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                ${this.escapeHtml(topic.content)}
                            </p>
                        </div>

                        <!-- Tips Section -->
                        ${topic.tips && topic.tips.length > 0 ? `
                            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">
                                            Tips & Panduan
                                        </h4>
                                        <ul class="text-sm text-blue-700 dark:text-blue-200 space-y-1">
                                            ${topic.tips.map(tip => `
                                                <li class="flex items-start">
                                                    <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>${this.escapeHtml(tip)}</span>
                                                </li>
                                            `).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        ` : ''}

                        <!-- Video Tutorial -->
                        ${topic.video ? `
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2 border-b border-gray-200 dark:border-gray-600">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        📹 Video Tutorial
                                    </h4>
                                </div>
                                <div class="aspect-w-16 aspect-h-9 bg-black">
                                    <video controls class="w-full" poster="/help/thumbnails/${topic.key}.jpg">
                                        <source src="${topic.video}" type="video/mp4">
                                        Browser Anda tidak mendukung video tag.
                                    </video>
                                </div>
                            </div>
                        ` : ''}

                        <!-- Image Guide -->
                        ${topic.image ? `
                            <div>
                                <img src="${topic.image}"
                                     alt="${this.escapeHtml(topic.title)}"
                                     class="w-full rounded-lg border border-gray-200 dark:border-gray-700">
                            </div>
                        ` : ''}

                        <!-- Documentation Link -->
                        ${topic.documentation ? `
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Dokumentasi Lengkap</span>
                                </div>
                                <a href="${topic.documentation}"
                                   target="_blank"
                                   class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                    Buka Dokumentasi →
                                </a>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Footer -->
                    <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <button type="button"
                                    id="help-search"
                                    class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                                🔍 Cari Bantuan Lain
                            </button>
                            <button type="button"
                                    id="help-close-btn"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Append to body
        document.body.appendChild(modal);

        // Setup event listeners
        this.setupModalListeners();

        // Trap focus
        this.trapFocus(modal);

        logger.info(`Help modal shown: ${topic.key}`);
    }

    /**
     * Setup modal event listeners
     */
    setupModalListeners() {
        // Close button (X)
        document.getElementById('help-close')?.addEventListener('click', () => this.hideHelp());

        // Close button (footer)
        document.getElementById('help-close-btn')?.addEventListener('click', () => this.hideHelp());

        // Backdrop click
        document.getElementById('help-backdrop')?.addEventListener('click', () => this.hideHelp());

        // Search button
        document.getElementById('help-search')?.addEventListener('click', () => {
            this.hideHelp();
            this.showSearchModal();
        });

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modalVisible) {
                this.hideHelp();
            }
        });
    }

    /**
     * Hide help modal
     */
    hideHelp() {
        const modal = document.getElementById('help-modal');
        if (modal) {
            modal.remove();
            this.modalVisible = false;
            this.currentTopic = null;
        }
    }

    /**
     * Show search modal
     */
    showSearchModal() {
        // Implementation for search modal
        logger.info('Search modal - coming soon');
    }

    /**
     * Show generic help when topic not found
     */
    showGenericHelp() {
        this.showHelp('general-help');
    }

    /**
     * Trap focus within modal for accessibility
     */
    trapFocus(element) {
        const focusable = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        element.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                } else if (!e.shiftKey && document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        // Focus first element
        first?.focus();
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Search help topics
     *
     * @param {string} query - Search query
     * @returns {Array} Matching topics
     */
    searchTopics(query) {
        const searchQuery = query.toLowerCase();

        return this.searchIndex
            .filter(item => item.searchable.includes(searchQuery))
            .slice(0, 10)
            .map(item => this.topics.get(item.key));
    }

    /**
     * Get all topics for a module
     *
     * @param {string} module - Module name
     * @returns {Array} Topics for module
     */
    getTopicsByModule(module) {
        return Array.from(this.topics.values())
            .filter(topic => topic.module === module)
            .sort((a, b) => a.order - b.order);
    }

    /**
     * Get all topics for a page
     *
     * @param {string} page - Page identifier
     * @returns {Array} Topics for page
     */
    getTopicsByPage(page) {
        return Array.from(this.topics.values())
            .filter(topic => topic.page === page)
            .sort((a, b) => a.order - b.order);
    }

    /**
     * Register topic from DOM element
     *
     * Usage: <div data-help-topic="customer-selection">
     */
    registerFromDOM() {
        const elements = document.querySelectorAll('[data-help-topic]');

        elements.forEach(el => {
            const topicKey = el.dataset.helpTopic;
            const title = el.dataset.helpTitle || '';
            const content = el.dataset.helpContent || '';

            if (topicKey && !this.topics.has(topicKey)) {
                this.registerTopic(topicKey, {
                    title,
                    content,
                    tips: el.dataset.helpTips?.split('|') || [],
                    video: el.dataset.helpVideo || null,
                });
            }
        });
    }

    /**
     * Load topic into modal (called from Alpine.js)
     *
     * @param {string} topicKey - Topic identifier
     */
    loadTopic(topicKey) {
        if (!topicKey) return;

        const topic = this.topics.get(topicKey);
        if (!topic) {
            logger.warn(`HelpSystem: Topic "${topicKey}" not found`);
            return;
        }

        this.currentTopic = topic;

        // Update modal content
        const titleEl = document.getElementById('help-title');
        const contentEl = document.getElementById('help-content');
        const tipsEl = document.getElementById('help-tips');
        const tipsListEl = document.getElementById('help-tips-list');
        const videoEl = document.getElementById('help-video');
        const docsEl = document.getElementById('help-docs');
        const docsLinkEl = document.getElementById('help-docs-link');

        if (titleEl) {
            titleEl.textContent = topic.title;
        }

        if (contentEl) {
            contentEl.innerHTML = `<p class="text-gray-700 dark:text-gray-300 leading-relaxed">${this.escapeHtml(topic.content)}</p>`;
        }

        // Show tips if available
        if (tipsEl && tipsListEl && topic.tips && topic.tips.length > 0) {
            tipsEl.classList.remove('hidden');
            tipsListEl.innerHTML = topic.tips.map(tip =>
                `<li class="flex items-start">
                    <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span>${this.escapeHtml(tip)}</span>
                </li>`
            ).join('');
        } else if (tipsEl) {
            tipsEl.classList.add('hidden');
        }

        // Show video if available
        if (videoEl && topic.video) {
            videoEl.classList.remove('hidden');
            // TODO: Add video player integration
        } else if (videoEl) {
            videoEl.classList.add('hidden');
        }

        // Show documentation link if available
        if (docsEl && docsLinkEl && topic.documentation) {
            docsEl.classList.remove('hidden');
            docsLinkEl.href = topic.documentation;
        } else if (docsEl) {
            docsEl.classList.add('hidden');
        }

        // Track usage
        this.trackUsage(topicKey);

        logger.info(`HelpSystem: Topic "${topicKey}" loaded in modal`);
    }

    /**
     * Open help search (called from modal)
     */
    openSearch() {
        // TODO: Implement search modal
        Dialog.alert('Fitur pencarian bantuan akan segera tersedia!');
    }

    /**
     * Track help topic usage
     *
     * @param {string} topicKey - Topic identifier
     */
    trackUsage(topicKey) {
        try {
            const usage = JSON.parse(localStorage.getItem('help_usage') || '{}');
            usage[topicKey] = (usage[topicKey] || 0) + 1;
            usage[topicKey + '_last'] = new Date().toISOString();
            localStorage.setItem('help_usage', JSON.stringify(usage));
        } catch (e) {
            logger.warn('HelpSystem: Failed to track usage', e);
        }
    }
}

// Create singleton
const helpSystem = new HelpSystem();

// Make globally available
window.helpSystem = helpSystem;
window.loadTopic = (topicKey) => helpSystem.loadTopic(topicKey);

// Auto-register topics from DOM
document.addEventListener('DOMContentLoaded', () => {
    helpSystem.registerFromDOM();
});

// Listen for help events
document.addEventListener('show-help', (e) => {
    const { topic } = e.detail;
    if (topic) {
        helpSystem.showHelp(topic);
    }
});

export default helpSystem;
export { HelpSystem };
