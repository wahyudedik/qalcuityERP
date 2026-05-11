/**
 * Custom Dialog System
 * Replaces native browser alert(), confirm(), and prompt() with styled modals.
 *
 * Usage:
 *   await Dialog.alert('Pesan berhasil disimpan!');
 *   await Dialog.success('Data tersimpan!');
 *   await Dialog.warning('Perhatian!');
 *   const ok = await Dialog.confirm('Hapus data ini?');
 *   const ok = await Dialog.danger('Hapus item ini?');
 *   const value = await Dialog.prompt('Masukkan nama:', 'Default');
 */

window.Dialog = {
    _container: null,

    _getContainer() {
        if (!this._container) {
            this._container = document.getElementById('custom-dialog-container');
        }
        if (!this._container) {
            this._container = document.createElement('div');
            this._container.id = 'custom-dialog-container';
            document.body.appendChild(this._container);
        }
        return this._container;
    },

    _icons: {
        info: `<svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
        success: `<svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
        warning: `<svg class="w-10 h-10 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
        danger: `<svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>`,
        question: `<svg class="w-10 h-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
    },

    /**
     * Show an alert dialog (informational)
     */
    alert(message, options = {}) {
        return this._show({
            type: 'alert',
            icon: options.icon || 'info',
            title: options.title || 'Informasi',
            message,
            confirmText: options.confirmText || 'OK',
            confirmClass: options.confirmClass || 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white',
        });
    },

    /**
     * Show a success alert
     */
    success(message, options = {}) {
        return this._show({
            type: 'alert',
            icon: 'success',
            title: options.title || 'Berhasil',
            message,
            confirmText: options.confirmText || 'OK',
            confirmClass: 'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white',
        });
    },

    /**
     * Show a warning alert
     */
    warning(message, options = {}) {
        return this._show({
            type: 'alert',
            icon: 'warning',
            title: options.title || 'Peringatan',
            message,
            confirmText: options.confirmText || 'OK',
            confirmClass: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500 text-white',
        });
    },

    /**
     * Show a confirm dialog
     */
    confirm(message, options = {}) {
        return this._show({
            type: 'confirm',
            icon: options.icon || 'question',
            title: options.title || 'Konfirmasi',
            message,
            confirmText: options.confirmText || 'Ya, Lanjutkan',
            cancelText: options.cancelText || 'Batal',
            confirmClass: options.confirmClass || 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white',
        });
    },

    /**
     * Show a danger confirm dialog (for destructive actions)
     */
    danger(message, options = {}) {
        return this._show({
            type: 'confirm',
            icon: 'danger',
            title: options.title || 'Konfirmasi Hapus',
            message,
            confirmText: options.confirmText || 'Ya, Hapus',
            cancelText: options.cancelText || 'Batal',
            confirmClass: 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white',
        });
    },

    /**
     * Show a prompt dialog
     */
    prompt(message, defaultValue = '', options = {}) {
        return this._show({
            type: 'prompt',
            icon: options.icon || 'question',
            title: options.title || 'Input',
            message,
            defaultValue,
            placeholder: options.placeholder || '',
            confirmText: options.confirmText || 'OK',
            cancelText: options.cancelText || 'Batal',
            confirmClass: options.confirmClass || 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white',
        });
    },

    /**
     * Internal: render and show the dialog
     */
    _show(config) {
        return new Promise((resolve) => {
            const container = this._getContainer();
            const dialogId = 'dialog-' + Date.now();
            const hasCancel = config.type === 'confirm' || config.type === 'prompt';
            const hasInput = config.type === 'prompt';

            container.innerHTML = `
                <div id="${dialogId}" class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
                     role="dialog" aria-modal="true" aria-labelledby="${dialogId}-title">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0"
                         data-backdrop></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm transform transition-all duration-300 scale-95 opacity-0"
                         data-panel>
                        <div class="p-6">
                            <div class="flex justify-center mb-4">
                                ${this._icons[config.icon] || this._icons.info}
                            </div>
                            <h3 id="${dialogId}-title" class="text-lg font-semibold text-gray-900 text-center mb-2">
                                ${config.title}
                            </h3>
                            <p class="text-sm text-gray-600 text-center leading-relaxed mb-5">
                                ${config.message}
                            </p>
                            ${hasInput ? `
                            <input type="text" data-input
                                   value="${this._escapeAttr(config.defaultValue || '')}"
                                   placeholder="${this._escapeAttr(config.placeholder || '')}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition mb-5" />
                            ` : ''}
                            <div class="flex gap-3 ${hasCancel ? '' : 'justify-center'}">
                                ${hasCancel ? `
                                <button data-cancel
                                        class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    ${config.cancelText}
                                </button>` : ''}
                                <button data-confirm
                                        class="flex-1 px-4 py-2.5 ${config.confirmClass} text-sm font-medium rounded-xl transition focus:outline-none focus:ring-2 focus:ring-offset-2">
                                    ${config.confirmText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;

            const dialog = document.getElementById(dialogId);
            const backdrop = dialog.querySelector('[data-backdrop]');
            const panel = dialog.querySelector('[data-panel]');
            const confirmBtn = dialog.querySelector('[data-confirm]');
            const cancelBtn = dialog.querySelector('[data-cancel]');
            const inputEl = dialog.querySelector('[data-input]');

            // Animate in
            requestAnimationFrame(() => {
                backdrop.classList.replace('opacity-0', 'opacity-100');
                panel.classList.remove('scale-95', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
            });

            // Focus
            setTimeout(() => {
                if (inputEl) { inputEl.focus(); inputEl.select(); }
                else confirmBtn.focus();
            }, 150);

            const closeDialog = (result) => {
                backdrop.classList.replace('opacity-100', 'opacity-0');
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                document.removeEventListener('keydown', onKeydown);
                setTimeout(() => { container.innerHTML = ''; resolve(result); }, 200);
            };

            const onKeydown = (e) => {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeDialog(config.type === 'prompt' ? null : config.type === 'confirm' ? false : undefined);
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (config.type === 'prompt') closeDialog(inputEl.value);
                    else if (config.type === 'confirm') closeDialog(true);
                    else closeDialog(undefined);
                }
            };
            document.addEventListener('keydown', onKeydown);

            confirmBtn.onclick = () => {
                if (config.type === 'prompt') closeDialog(inputEl.value);
                else if (config.type === 'confirm') closeDialog(true);
                else closeDialog(undefined);
            };

            if (cancelBtn) {
                cancelBtn.onclick = () => closeDialog(config.type === 'prompt' ? null : false);
            }

            backdrop.onclick = () => {
                closeDialog(config.type === 'prompt' ? null : config.type === 'confirm' ? false : undefined);
            };
        });
    },

    _escapeAttr(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML.replace(/"/g, '&quot;');
    },
};

// ═══════════════════════════════════════════════════════════
// Global Event Delegation for data-confirm attributes
// Usage in Blade: <form data-confirm="Hapus data ini?"> or <button data-confirm="Yakin?">
// ═══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    // Handle form submissions with data-confirm
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('form[data-confirm]');
        if (!form) return;

        e.preventDefault();
        const message = form.dataset.confirm;
        const isDanger = form.dataset.confirmType === 'danger';

        const confirmed = isDanger
            ? await Dialog.danger(message)
            : await Dialog.confirm(message);

        if (confirmed) {
            const msg = form.dataset.confirm;
            delete form.dataset.confirm;
            form.submit();
            form.dataset.confirm = msg;
        }
    });

    // Handle button/link clicks with data-confirm
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-confirm]:not(form)');
        if (!el) return;
        if (el.tagName === 'FORM') return;

        e.preventDefault();
        e.stopPropagation();
        const message = el.dataset.confirm;
        const isDanger = el.dataset.confirmType === 'danger';

        const confirmed = isDanger
            ? await Dialog.danger(message)
            : await Dialog.confirm(message);

        if (confirmed) {
            const form = el.closest('form');
            if (form && (el.type === 'submit' || el.tagName === 'BUTTON')) {
                form.submit();
            } else if (el.tagName === 'A') {
                window.location.href = el.href;
            }
        }
    });
});
