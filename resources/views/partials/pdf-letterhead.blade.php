{{--
    Komponen kop surat PDF reusable.
    Variabel yang dibutuhkan: $tenant (Tenant model)
    Variabel opsional: $docTitle (string), $docSubtitle (string)
--}}
@php
    $color = $tenant->letter_head_color ?? '#1d4ed8';
    $logoUrl = $tenant->logo ? Storage::disk('public')->url($tenant->logo) : null;
@endphp
<style>
    .lh-wrap { border-bottom: 3px solid {{ $color }}; padding-bottom: 12px; margin-bottom: 18px; }
    .lh-inner { display: flex; justify-content: space-between; align-items: flex-start; }
    .lh-logo { max-height: 60px; max-width: 160px; object-fit: contain; }
    .lh-company { flex: 1; padding-left: {{ $logoUrl ? '16px' : '0' }}; }
    .lh-company-name { font-size: 16px; font-weight: bold; color: {{ $color }}; }
    .lh-company-tagline { font-size: 9px; color: #6b7280; margin-top: 1px; }
    .lh-company-detail { font-size: 9px; color: #374151; margin-top: 4px; line-height: 1.6; }
    .lh-doc-title { text-align: right; }
    .lh-doc-title h2 { font-size: 18px; font-weight: bold; color: {{ $color }}; text-transform: uppercase; letter-spacing: 1px; }
    .lh-doc-title p { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .lh-npwp { font-size: 9px; color: #6b7280; margin-top: 2px; }
</style>

<div class="lh-wrap">
    <div class="lh-inner">
        <div style="display:flex;align-items:flex-start;">
            @if($logoUrl)
            <img src="{{ $logoUrl }}" class="lh-logo" alt="{{ $tenant->name }}">
            @endif
            <div class="lh-company">
                <div class="lh-company-name">{{ $tenant->name }}</div>
                @if($tenant->tagline)
                <div class="lh-company-tagline">{{ $tenant->tagline }}</div>
                @endif
                <div class="lh-company-detail">
                    @if($tenant->address){{ $tenant->address }}@endif
                    @if($tenant->city || $tenant->province)
                    , {{ $tenant->city }}{{ $tenant->province ? ', ' . $tenant->province : '' }}
                    @endif
                    @if($tenant->postal_code) {{ $tenant->postal_code }}@endif
                    @if($tenant->phone)<br>Telp: {{ $tenant->phone }}@endif
                    @if($tenant->email) | Email: {{ $tenant->email }}@endif
                    @if($tenant->website)<br>{{ $tenant->website }}@endif
                </div>
                @if($tenant->npwp)
                <div class="lh-npwp">NPWP: {{ $tenant->npwp }}</div>
                @endif
            </div>
        </div>
        @if(isset($docTitle))
        <div class="lh-doc-title">
            <h2>{{ $docTitle }}</h2>
            @if(isset($docSubtitle))<p>{{ $docSubtitle }}</p>@endif
        </div>
        @endif
    </div>
</div>
