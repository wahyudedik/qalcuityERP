@extends('layouts.app')

@section('title', 'Izin Akses — ' . $user->name)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Izin Akses: {{ $user->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Role: <span class="font-medium text-blue-600">{{ $user->roleLabel() }}</span>
                &mdash; Centang untuk mengizinkan, kosongkan untuk menolak.
                Override ini menggantikan default role.
            </p>
        </div>
        <a href="{{ route('tenant.users.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            &larr; Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('tenant.users.permissions.save', $user) }}">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-300 w-48">Modul</th>
                        @foreach(['view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Edit', 'delete' => 'Hapus'] as $act => $label)
                            <th class="text-center px-3 py-3 font-medium text-gray-600 dark:text-gray-300 w-20">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($modules as $module => $actions)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                                {{ \App\Services\PermissionService::moduleLabel($module) }}
                            </td>
                            @foreach(['view', 'create', 'edit', 'delete'] as $action)
                                <td class="text-center px-3 py-3">
                                    @if(in_array($action, $actions))
                                        @php
                                            $key     = "{$module}.{$action}";
                                            $current = $userPerms[$module][$action] ?? false;
                                        @endphp
                                        <input type="checkbox"
                                               name="perms[{{ $key }}]"
                                               value="1"
                                               {{ $current ? 'checked' : '' }}
                                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                Simpan Izin
            </button>

            <button type="button"
                    onclick="document.getElementById('reset-form').submit()"
                    class="px-5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                Reset ke Default Role
            </button>
        </div>
    </form>

    {{-- Hidden reset form --}}
    <form id="reset-form" method="POST"
          action="{{ route('tenant.users.permissions.reset', $user) }}"
          onsubmit="return confirm('Reset semua izin ke default role {{ $user->roleLabel() }}?')">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection
