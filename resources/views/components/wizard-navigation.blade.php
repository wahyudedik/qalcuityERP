@props([
    'prevText' => 'Sebelumnya',
    'nextText' => 'Selanjutnya',
    'submitText' => 'Simpan',
    'draftText' => 'Simpan Draft',
    'showDraft' => true,
])

{{-- 
    Wizard Navigation Component
    Navigation buttons for form wizard
    
    Usage:
    <x-wizard-navigation />
    <x-wizard-navigation prev-text="Kembali" next-text="Lanjut" submit-text="Submit" />
--}}

<div class="flex items-center justify-between gap-3">
    <div class="flex gap-2">
        @if ($showDraft)
            <button type="button" data-wizard-save-draft
                class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition font-medium">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                {{ $draftText }}
            </button>
        @endif
    </div>

    <div class="flex gap-3">
        <button type="button" data-wizard-prev
            class="px-6 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition font-medium"
            style="display: none;">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ $prevText }}
        </button>

        <button type="button" data-wizard-next
            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            {{ $nextText }}
            <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <button type="submit"
            class="px-6 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition"
            style="display: none;">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ $submitText }}
        </button>
    </div>
</div>
