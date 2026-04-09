{{-- Status Badge Component --}}
@props([
    'status' => 'unknown',
    'type' => 'default' // default, priority, triage
])

@php
$styles = [
    // Default statuses
    'pending' => 'bg-yellow-100 text-yellow-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-gray-100 text-gray-800',
    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-gray-100 text-gray-800',
    'scheduled' => 'bg-purple-100 text-purple-800',
    'verified' => 'bg-green-100 text-green-800',
    'draft' => 'bg-gray-100 text-gray-600',
    'submitted' => 'bg-blue-100 text-blue-800',
    'approved' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-red-100 text-red-800',
    'paid' => 'bg-green-100 text-green-800',
    'unpaid' => 'bg-red-100 text-red-800',
    
    // Priority levels
    'low' => 'bg-green-100 text-green-800',
    'normal' => 'bg-blue-100 text-blue-800',
    'urgent' => 'bg-orange-100 text-orange-800',
    'critical' => 'bg-red-100 text-red-800',
    'emergency' => 'bg-red-200 text-red-900',
    'stat' => 'bg-red-200 text-red-900',
    
    // Triage levels
    'red' => 'bg-red-200 text-red-900 border-2 border-red-400',
    'yellow' => 'bg-yellow-200 text-yellow-900 border-2 border-yellow-400',
    'green' => 'bg-green-200 text-green-900 border-2 border-green-400',
    'black' => 'bg-gray-800 text-white border-2 border-gray-600',
];

$style = $styles[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $style }}">
    @if($type === 'triage')
        @if($status === 'red')
        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        @elseif($status === 'black')
        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        @endif
    @endif
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
