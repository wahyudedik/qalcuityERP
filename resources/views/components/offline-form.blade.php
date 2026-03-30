{{--
    Offline-aware form wrapper.
    Intercepts submit when offline, queues mutation, shows feedback.

    Usage:
        <x-offline-form action="/invoices" method="POST" module="invoices">
            ... form fields ...
            <button type="submit">Simpan</button>
        </x-offline-form>
--}}
@props([
    'action',
    'method' => 'POST',
    'module' => 'general',
    'redirect' => null,
    'offlineMessage' => 'Disimpan offline. Akan disinkronisasi saat online.',
])

<form
    {{ $attributes->merge(['class' => 'offline-form']) }}
    action="{{ $action }}"
    method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}"
    data-offline-module="{{ $module }}"
    data-offline-redirect="{{ $redirect }}"
    data-offline-message="{{ $offlineMessage }}"
    data-real-method="{{ strtoupper($method) }}"
>
    @csrf
    @if(!in_array(strtoupper($method), ['GET', 'POST']))
        @method($method)
    @endif

    {{ $slot }}
</form>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.offline-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            // If online, let the form submit normally
            if (navigator.onLine) return;

            e.preventDefault();

            const module = this.dataset.offlineModule;
            const action = this.action;
            const method = this.dataset.realMethod || 'POST';
            const message = this.dataset.offlineMessage;
            const redirect = this.dataset.offlineRedirect;

            // Collect form data as JSON
            const formData = new FormData(this);
            const payload = {};
            for (const [key, value] of formData.entries()) {
                if (key === '_token' || key === '_method') continue;
                // Handle array fields (e.g. items[0][qty])
                if (key.includes('[')) {
                    setNestedValue(payload, key, value);
                } else {
                    payload[key] = value;
                }
            }

            try {
                const queueId = await window.ErpOffline.queue(module, action, method, payload);

                // Show success feedback
                if (typeof window.showToast === 'function') {
                    window.showToast(message, 'warning');
                } else if (typeof window.ErpOffline !== 'undefined') {
                    // Use offline manager toast
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 z-[9999] px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg bg-amber-500/90 transition-all duration-300';
                    toast.innerHTML = `⚡ ${message}`;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 4000);
                }

                // Redirect if specified
                if (redirect) {
                    setTimeout(() => window.location.href = redirect, 1000);
                }
            } catch (err) {
                console.error('Offline queue error:', err);
                alert('Gagal menyimpan data offline. Silakan coba lagi.');
            }
        });
    });

    // Helper: set nested object value from bracket notation
    function setNestedValue(obj, path, value) {
        const keys = path.replace(/\]/g, '').split('[');
        let current = obj;
        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];
            if (!current[key]) {
                current[key] = isNaN(keys[i + 1]) ? {} : [];
            }
            current = current[key];
        }
        current[keys[keys.length - 1]] = value;
    }
});
</script>
@endpush
@endonce
