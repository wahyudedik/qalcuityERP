@extends('layouts.app')
@section('title', 'Webhook Delivery Log')

@section('content')
<div class="p-6 max-w-5xl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Webhook Delivery Log</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Riwayat pengiriman webhook outbound ke endpoint pihak ketiga.</p>
        </div>
        <a href="{{ route('api-settings.index') }}" class="text-xs text-blue-500 hover:text-blue-600 transition">← Kembali ke API Settings</a>
    </div>

    @if($deliveries->isEmpty())
    <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <p class="text-sm text-gray-400 dark:text-slate-500">Belum ada delivery log.</p>
    </div>
    @else
    <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Event</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Webhook</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">HTTP</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Durasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($deliveries as $d)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <td class="px-4 py-3">
                            <code class="text-xs font-mono text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-1.5 py-0.5 rounded">{{ $d->event }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-gray-700 dark:text-slate-300 font-medium">{{ $d->subscription?->name ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 truncate max-w-[200px]">{{ $d->subscription?->url }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($d->status === 'success')
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Success
                            </span>
                            @elseif($d->status === 'pending')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">Pending</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Failed
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400 font-mono">
                            {{ $d->response_code ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                            {{ $d->duration_ms ? $d->duration_ms . 'ms' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                            {{ $d->created_at->format('d M H:i:s') }}
                            <span class="text-[10px] text-gray-400">(#{{ $d->attempt }})</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($d->status === 'failed')
                            <form method="POST" action="{{ route('api-settings.webhooks.deliveries.retry', $d) }}">
                                @csrf
                                <button type="submit" class="text-xs text-blue-500 hover:text-blue-600 transition">Retry</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $deliveries->links() }}
    </div>
    @endif
</div>
@endsection
