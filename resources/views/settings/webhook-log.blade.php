@extends('layouts.app')
@section('title', 'Webhook Delivery Log')

@section('content')
<div class="p-6 max-w-5xl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Webhook Delivery Log</h1>
            <p class="text-sm text-gray-500 mt-0.5">Riwayat pengiriman webhook outbound ke endpoint pihak ketiga.</p>
        </div>
        <a href="{{ route('api-settings.index') }}" class="text-xs text-blue-500 hover:text-blue-600 transition">← Kembali ke API Settings</a>
    </div>

    @if($deliveries->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-sm text-gray-400">Belum ada delivery log.</p>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Event</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Webhook</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">HTTP</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Durasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($deliveries ?? [] as $d)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <code class="text-xs font-mono text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">{{ $d->event }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-gray-700 font-medium">{{ $d->subscription?->name ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 truncate max-w-[200px]">{{ $d->subscription?->url }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($d->status === 'success')
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Success
                            </span>
                            @elseif($d->status === 'pending')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-600">Pending</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Failed
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 font-mono">
                            {{ $d->response_code ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $d->duration_ms ? $d->duration_ms . 'ms' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
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
