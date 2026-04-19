@props([
    'action' => 'view', // view, create, edit, delete, approve, export
    'module' => null,
    'href' => null,
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, success
    'size' => 'md', // sm, md, lg
    'icon' => null,
])

@php
    // TASK 8.3: Check if user has permission for this action
    $user = auth()->user();
    
    // SuperAdmin bypasses all checks
    if ($user && $user->isSuperAdmin()) {
        $canPerform = true;
    } elseif ($module && $user) {
        // Check permission via PermissionService
        $canPerform = $user->hasPermission($module, $action);
    } else {
        // No module specified or not authenticated
        $canPerform = true;
    }

    // Don't render button if user doesn't have permission
    if (!$canPerform) {
        return;
    }

    // Determine button classes based on variant and size
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-900 focus:ring-gray-500 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600',
        'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600',
        'warning' => 'bg-amber-600 hover:bg-amber-700 text-white focus:ring-amber-500 dark:bg-amber-500 dark:hover:bg-amber-600',
        default => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };
    
    $classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
    
    // Merge with any additional classes from $attributes
    $classes = $attributes->get('class') ? $classes . ' ' . $attributes->get('class') : $classes;
    
    // Icon mapping for common actions
    $iconMap = [
        'create' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
        'edit' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
        'delete' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
        'view' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
        'approve' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'export' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    ];
    
    $iconHtml = $icon ?? ($iconMap[$action] ?? null);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconHtml)
            <span class="mr-2">{!! $iconHtml !!}</span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($iconHtml)
            <span class="mr-2">{!! $iconHtml !!}</span>
        @endif
        {{ $slot }}
    </button>
@endif
