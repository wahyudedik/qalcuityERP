@extends('layouts.app')

@section('title', 'Project Billing Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Project Billing Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">
                Kelola billing dan invoice untuk semua project
            </p>
        </div>

        @if ($projects->count() > 0)
            <!-- Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($projects as $project)
                    <div
                        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <!-- Project Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                                        {{ $project->name }}
                                    </h3>
                                    @if ($project->customer)
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ $project->customer->name }}
                                        </p>
                                    @endif
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $project->status === 'active'
                                    ? 'bg-green-100 text-green-800'
                                    : ($project->status === 'completed'
                                        ? 'bg-blue-100 text-blue-800'
                                        : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($project->status ?? 'draft') }}
                                </span>
                            </div>

                            <!-- Billing Info -->
                            <div class="space-y-3 mb-4">
                                @if ($project->billingConfig)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500">Billing Type:</span>
                                        <span class="font-medium text-gray-900 capitalize">
                                            {{ str_replace('_', ' ', $project->billingConfig->billing_type) }}
                                        </span>
                                    </div>

                                    @if ($project->billingConfig->hourly_rate)
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500">Hourly Rate:</span>
                                            <span class="font-medium text-gray-900">
                                                Rp {{ number_format($project->billingConfig->hourly_rate, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @endif
                                @else
                                    <div
                                        class="text-sm text-yellow-600 bg-yellow-50 p-2 rounded">
                                        ⚠️ Billing config belum disetup
                                    </div>
                                @endif
                            </div>

                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-500">Total Invoices</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $project->projectInvoices->count() }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Unbilled Hours</p>
                                    <p class="text-lg font-semibold text-orange-600">
                                        {{ $project->timesheets()->where('billing_status', 'unbilled')->sum('hours') }}h
                                    </p>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <a href="{{ route('project-billing.show', $project) }}"
                                    class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Manage Billing
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $projects->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div
                class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No projects found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Belum ada project yang dibuat. Mulai dengan membuat project baru.
                </p>
                <div class="mt-6">
                    <a href="{{ route('projects.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Create First Project
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
