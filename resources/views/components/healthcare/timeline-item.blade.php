{{-- Timeline Item Component --}}
@props([
    'date' => null,
    'title' => '',
    'description' => '',
    'icon' => 'clock',
    'color' => 'blue',
    'isLast' => false
])

@php
$colors = [
    'blue' => 'bg-blue-500',
    'green' => 'bg-green-500',
    'yellow' => 'bg-yellow-500',
    'red' => 'bg-red-500',
    'purple' => 'bg-purple-500',
    'gray' => 'bg-gray-500',
];

$icons = [
    'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
    'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    'alert' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
    'document' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
    'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>',
    'flask' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>',
];

$colorClass = $colors[$color] ?? $colors['blue'];
$iconSvg = $icons[$icon] ?? $icons['clock'];
@endphp

<div class="relative">
    @if(!$isLast)
    <div class="absolute left-4 top-8 -bottom-0.5 w-0.5 bg-gray-200"></div>
    @endif
    
    <div class="relative flex items-start space-x-3">
        <div class="relative">
            <div class="h-8 w-8 rounded-full {{ $colorClass }} flex items-center justify-center ring-8 ring-white">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $iconSvg !!}
                </svg>
            </div>
        </div>
        
        <div class="min-w-0 flex-1">
            <div class="text-sm">
                <div class="font-medium text-gray-900">{{ $title }}</div>
                @if($date)
                <div class="text-gray-500 mt-0.5">
                    {{ \Carbon\Carbon::parse($date)->format('M d, Y H:i') }}
                </div>
                @endif
            </div>
            
            @if($description)
            <div class="mt-1 text-sm text-gray-700">
                {{ $description }}
            </div>
            @endif
            
            @isset($actions)
            <div class="mt-2">
                {{ $actions }}
            </div>
            @endisset
        </div>
    </div>
</div>
