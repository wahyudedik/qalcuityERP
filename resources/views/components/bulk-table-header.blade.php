{{-- 
    Bulk Table Header Component
    Usage: Di dalam <thead><tr>, taruh di kolom pertama
--}}

@props(['selectAllId' => 'select-all'])

<th class="px-6 py-3 text-left w-12">
    <input type="checkbox" id="{{ $selectAllId }}"
        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
        onclick="toggleAllCheckboxes(this)">
</th>

<script>
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');
        const bulkBar = document.getElementById('bulk-action-bar');
        const countSpan = document.getElementById('selected-count');

        if (bulkBar) {
            if (checkboxes.length > 0) {
                bulkBar.classList.remove('hidden');
                countSpan.textContent = checkboxes.length;
            } else {
                bulkBar.classList.add('hidden');
            }
        }
    }
</script>
