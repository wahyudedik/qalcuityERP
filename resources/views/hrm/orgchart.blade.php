<x-app-layout>
    <x-slot name="title">Struktur Organisasi — Qalcuity ERP</x-slot>
    <x-slot name="header">Struktur Organisasi</x-slot>

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-slate-400">
            {{ $employees->count() }} karyawan aktif. Klik karyawan untuk mengatur atasan.
        </p>
        <div class="flex gap-2">
            <button onclick="expandAll()" class="px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Buka Semua</button>
            <button onclick="collapseAll()" class="px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Tutup Semua</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Org Chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 overflow-auto min-h-96">
            <div id="org-tree" class="flex justify-center">
                <div class="text-sm text-gray-400 dark:text-slate-500 py-8">Memuat struktur...</div>
            </div>
        </div>

        {{-- Edit Manager Panel --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Atur Atasan</h3>
            <form id="form-manager" method="POST" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Karyawan</label>
                    <select id="sel-employee" name="_employee_id" onchange="onEmployeeChange()"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Pilih karyawan...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" data-manager="{{ $emp->manager_id ?? '' }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Atasan Langsung</label>
                    <select id="sel-manager" name="manager_id"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Tidak ada (Top Level)</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    Simpan Struktur
                </button>
            </form>

            {{-- Unassigned employees --}}
            <div class="mt-6">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-3">Tanpa Atasan</p>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    @foreach($employees->whereNull('manager_id') as $emp)
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-500/10"
                         onclick="selectEmployee({{ $emp->id }})">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($emp->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $emp->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $emp->position ?? '-' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const employees = @json($nodes);

    // Build tree
    function buildTree(nodes, parentId = null) {
        return nodes
            .filter(n => (n.manager_id ?? null) == parentId)
            .map(n => ({ ...n, children: buildTree(nodes, n.id) }));
    }

    function renderNode(node) {
        const deptColor = stringToColor(node.department || 'default');
        const hasChildren = node.children.length > 0;
        const childrenId = 'children-' + node.id;

        return `
        <div class="flex flex-col items-center">
            <div class="org-node group relative bg-white dark:bg-[#0f172a] border-2 border-gray-200 dark:border-white/10 rounded-xl px-4 py-3 min-w-[140px] max-w-[180px] text-center shadow-sm hover:border-blue-400 hover:shadow-md transition cursor-pointer"
                 onclick="selectEmployee(${node.id})" style="border-top: 3px solid ${deptColor}">
                <div class="w-8 h-8 rounded-full mx-auto mb-1 flex items-center justify-center text-white text-sm font-bold"
                     style="background: ${deptColor}">
                    ${node.name.charAt(0).toUpperCase()}
                </div>
                <p class="text-xs font-semibold text-gray-900 dark:text-white leading-tight">${node.name}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 truncate">${node.position || '—'}</p>
                ${node.department ? `<span class="inline-block mt-1 px-1.5 py-0.5 rounded text-xs" style="background:${deptColor}22;color:${deptColor}">${node.department}</span>` : ''}
                ${hasChildren ? `<button onclick="event.stopPropagation();toggleChildren('${childrenId}')" class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center shadow hover:bg-blue-600 z-10" id="btn-${childrenId}">▼</button>` : ''}
            </div>
            ${hasChildren ? `
            <div class="relative mt-3 pt-3 border-t-2 border-gray-200 dark:border-white/10" id="${childrenId}">
                <div class="flex gap-6 items-start">
                    ${node.children.map(c => renderNode(c)).join('')}
                </div>
            </div>` : ''}
        </div>`;
    }

    function stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
        const h = Math.abs(hash) % 360;
        return `hsl(${h}, 60%, 50%)`;
    }

    function toggleChildren(id) {
        const el = document.getElementById(id);
        const btn = document.getElementById('btn-' + id);
        if (el) {
            const hidden = el.classList.toggle('hidden');
            if (btn) btn.textContent = hidden ? '▶' : '▼';
        }
    }

    function expandAll() {
        document.querySelectorAll('[id^="children-"]').forEach(el => el.classList.remove('hidden'));
        document.querySelectorAll('[id^="btn-children-"]').forEach(btn => btn.textContent = '▼');
    }

    function collapseAll() {
        document.querySelectorAll('[id^="children-"]').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('[id^="btn-children-"]').forEach(btn => btn.textContent = '▶');
    }

    function selectEmployee(id) {
        const sel = document.getElementById('sel-employee');
        sel.value = id;
        onEmployeeChange();
        // Scroll to panel on mobile
        document.getElementById('form-manager').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function onEmployeeChange() {
        const sel = document.getElementById('sel-employee');
        const opt = sel.options[sel.selectedIndex];
        const managerId = opt?.dataset?.manager || '';
        const empId = sel.value;

        // Update form action
        document.getElementById('form-manager').action = '{{ url("hrm") }}/' + empId + '/manager';

        // Set current manager
        const mgrSel = document.getElementById('sel-manager');
        mgrSel.value = managerId;

        // Remove self from manager options
        Array.from(mgrSel.options).forEach(o => {
            o.disabled = o.value == empId;
        });
    }

    // Render tree on load
    const tree = buildTree(employees);
    const container = document.getElementById('org-tree');
    if (tree.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-400 dark:text-slate-500 py-8">Belum ada karyawan aktif.</p>';
    } else {
        container.innerHTML = `<div class="flex gap-8 items-start flex-wrap justify-center">${tree.map(n => renderNode(n)).join('')}</div>`;
    }
    </script>
    @endpush
</x-app-layout>
