@props([
    'module' => 'products',
    'onApply' => null,
])

<!-- Advanced Filter Builder Component -->
<div x-data="filterBuilder('{{ $module }}')" class="space-y-4">
    <!-- Filter Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Filter Lanjutan</h3>
        <button @click="addFilter()"
            class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Filter
        </button>
    </div>

    <!-- Active Filters -->
    <div class="space-y-2">
        <template x-for="(filter, index) in filters" :key="index">
            <div
                class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <!-- Field Select -->
                <select x-model="filter.field" @change="updateOperators(index)"
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Field</option>
                    <template x-for="field in availableFields" :key="field.value">
                        <option :value="field.value" x-text="field.label"></option>
                    </template>
                </select>

                <!-- Operator Select -->
                <select x-model="filter.operator"
                    class="w-32 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <template x-for="op in filter.operators" :key="op.value">
                        <option :value="op.value" x-text="op.label"></option>
                    </template>
                </select>

                <!-- Value Input -->
                <input type="text" x-model="filter.value" placeholder="Nilai"
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">

                <!-- Remove Button -->
                <button @click="removeFilter(index)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Hapus filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </template>

        <!-- Empty State -->
        <div x-show="filters.length === 0" class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                </path>
            </svg>
            <p class="text-sm">Belum ada filter. Klik "Tambah Filter" untuk memulai.</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div x-show="filters.length > 0" class="flex items-center gap-2 pt-2 border-t border-gray-200">
        <button @click="applyFilters()"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            Terapkan Filter
        </button>
        <button @click="saveAsSearch()"
            class="px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
            Simpan Pencarian
        </button>
        <button @click="clearFilters()"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
            Reset
        </button>
    </div>
</div>

<script>
    function filterBuilder(module) {
        return {
            module: module,
            filters: [],
            availableFields: [],
            allOperators: [{
                    value: 'equals',
                    label: '='
                },
                {
                    value: 'not_equals',
                    label: '!='
                },
                {
                    value: 'contains',
                    label: 'mengandung'
                },
                {
                    value: 'not_contains',
                    label: 'tidak mengandung'
                },
                {
                    value: 'greater_than',
                    label: '>'
                },
                {
                    value: 'less_than',
                    label: '<'
                },
                {
                    value: 'greater_than_equals',
                    label: '>='
                },
                {
                    value: 'less_than_equals',
                    label: '<='
                },
                {
                    value: 'in',
                    label: 'dalam'
                },
                {
                    value: 'not_in',
                    label: 'tidak dalam'
                },
                {
                    value: 'is_null',
                    label: 'null'
                },
                {
                    value: 'is_not_null',
                    label: 'tidak null'
                },
            ],

            init() {
                this.loadAvailableFields();
            },

            loadAvailableFields() {
                const fieldMap = {
                    products: [{
                            value: 'name',
                            label: 'Nama Produk'
                        },
                        {
                            value: 'sku',
                            label: 'SKU'
                        },
                        {
                            value: 'price',
                            label: 'Harga'
                        },
                        {
                            value: 'stock',
                            label: 'Stok'
                        },
                        {
                            value: 'category_id',
                            label: 'Kategori'
                        },
                    ],
                    invoices: [{
                            value: 'number',
                            label: 'Nomor Invoice'
                        },
                        {
                            value: 'status',
                            label: 'Status'
                        },
                        {
                            value: 'total',
                            label: 'Total'
                        },
                        {
                            value: 'due_date',
                            label: 'Tanggal Jatuh Tempo'
                        },
                        {
                            value: 'customer_id',
                            label: 'Pelanggan'
                        },
                    ],
                    customers: [{
                            value: 'name',
                            label: 'Nama Pelanggan'
                        },
                        {
                            value: 'email',
                            label: 'Email'
                        },
                        {
                            value: 'phone',
                            label: 'Telepon'
                        },
                        {
                            value: 'city',
                            label: 'Kota'
                        },
                    ],
                };

                this.availableFields = fieldMap[this.module] || [];
            },

            addFilter() {
                this.filters.push({
                    field: '',
                    operator: 'equals',
                    operators: this.allOperators,
                    value: '',
                });
            },

            removeFilter(index) {
                this.filters.splice(index, 1);
            },

            updateOperators(index) {
                const field = this.availableFields.find(f => f.value === this.filters[index].field);
                if (field) {
                    // Could customize operators based on field type
                    this.filters[index].operators = this.allOperators;
                }
            },

            applyFilters() {
                const filterData = this.filters
                    .filter(f => f.field && f.value)
                    .reduce((acc, f) => {
                        acc[f.field] = f.value;
                        return acc;
                    }, {});

                // Dispatch event to parent
                this.$dispatch('filters-applied', {
                    module: this.module,
                    filters: filterData,
                });

                // Call onApply callback if provided
                @if ($onApply)
                    {{ $onApply }}(filterData);
                @endif
            },

            saveAsSearch() {
                const query = prompt('Nama pencarian yang disimpan:');
                if (!query) return;

                const filterData = this.filters
                    .filter(f => f.field && f.value)
                    .reduce((acc, f) => {
                        acc[f.field] = f.value;
                        return acc;
                    }, {});

                fetch('/api/saved-searches', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            name: query,
                            query: query,
                            type: this.module,
                            filters: filterData,
                            module: this.module,
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Pencarian berhasil disimpan!');
                        }
                    })
                    .catch(err => {
                        console.error('Failed to save search:', err);
                    });
            },

            clearFilters() {
                this.filters = [];
            },
        };
    }
</script>
