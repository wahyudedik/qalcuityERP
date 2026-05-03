{{-- 
    Bulk Action Bar Component
    Usage: Taruh di atas tabel untuk menampilkan bulk actions
--}}

@props(['actions' => []])

<div id="bulk-action-bar"
    class="hidden mb-4 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-700">
                <span id="selected-count" class="font-semibold text-blue-600">0</span>
                item dipilih
            </span>
        </div>
        <div class="flex items-center gap-2">
            @foreach ($actions as $action)
                @if (isset($action['danger']))
                    <button type="button" onclick="{{ $action['onclick'] ?? '' }}"
                        class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition">
                        {{ $action['label'] }}
                    </button>
                @else
                    <button type="button" onclick="{{ $action['onclick'] ?? '' }}"
                        class="px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50 transition">
                        {{ $action['label'] }}
                    </button>
                @endif
            @endforeach
            <button type="button" onclick="clearSelection()"
                class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
    function clearSelection() {
        const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
        const selectAll = document.getElementById('select-all');

        checkboxes.forEach(checkbox => checkbox.checked = false);
        if (selectAll) selectAll.checked = false;

        updateBulkBar();
    }
</script>
