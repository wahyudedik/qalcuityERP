{{--
    Task 3: Launcher Overlay â€” Grid Modul + Search + Recently Visited
    Overlay launcher untuk memilih modul
--}}
{{-- Backdrop fullscreen (mobile) / click-outside handler --}}
<div x-data id="launcher-overlay" x-show="$store.navSystem.launcherOpen" role="dialog" aria-modal="true"
    aria-label="Pilih Modul" @keydown.escape.window="$store.navSystem.closeLauncher()" class="fixed inset-0 z-[100]"
    style="display: none;" x-cloak>
    {{-- Backdrop: fullscreen on mobile, click-outside on desktop --}}
    <div class="absolute inset-0 lg:bg-transparent bg-black/40" @click="$store.navSystem.closeLauncher()"
        aria-hidden="true"></div>

    {{-- Panel konten --}}
    <div x-show="$store.navSystem.launcherOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute bg-white shadow-2xl border border-gray-200 flex flex-col
                   top-14 left-0 rounded-2xl w-[480px]
                   max-lg:w-full max-lg:right-0 max-lg:rounded-none max-lg:bottom-0"
        style="transform-origin: top left; max-height: calc(100vh - 4rem);" @click.stop>

        {{-- Header: Search + Close button (mobile) â€” sticky --}}
        <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 shrink-0">
            {{-- Search bar --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input id="launcher-search" type="text" role="searchbox" aria-label="Cari modul"
                    placeholder="Cari modul..." x-model="$store.navSystem.launcherQuery"
                    x-effect="if ($store.navSystem.launcherOpen) $nextTick(() => $el.focus())"
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl bg-gray-100
                               border border-transparent focus:border-gray-300
                               text-gray-700 placeholder-gray-400 outline-none transition">
            </div>

            {{-- Close button (mobile only) --}}
            <button @click="$store.navSystem.closeLauncher()"
                class="lg:hidden flex items-center justify-center w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-500 transition"
                aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Scrollable body: Module Grid --}}
        <div class="flex-1 overflow-y-auto overscroll-contain" style="scrollbar-width: thin;">
            <div class="p-4">
                {{-- Empty state --}}
                <p x-show="$store.navSystem.filteredModules.length === 0"
                    class="text-sm text-gray-400 text-center py-6">
                    Tidak ada modul yang cocok
                </p>

                {{-- Grid: 3 kolom desktop (>=768px), 2 kolom mobile (<768px) --}}
                <div x-show="$store.navSystem.filteredModules.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-2"
                    role="list" aria-label="Daftar modul">
                    <template x-for="(module, index) in $store.navSystem.filteredModules" :key="module.key">
                        <button role="listitem" :aria-pressed="module.key === $store.navSystem.activeModule"
                            @click="$store.navSystem.selectModule(module.key)"
                            :class="module.key === $store.navSystem.activeModule ?
                                'ring-2 ring-offset-1' :
                                'hover:bg-gray-50'"
                            :style="module.key === $store.navSystem.activeModule ?
                                `ring-color: var(--module-color-${module.key}); background-color: color-mix(in srgb, var(--module-color-${module.key}) 10%, transparent)` :
                                ''"
                            class="flex flex-col items-center gap-2 p-4 rounded-2xl transition-all duration-150 text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{-- Icon container --}}
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                :style="`background-color: color-mix(in srgb, var(--module-color-${module.key}) 15%, transparent)`">
                                {{-- home icon --}}
                                <template x-if="module.icon === 'home'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </template>
                                {{-- sparkle/ai icon --}}
                                <template x-if="module.icon === 'sparkle'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </template>
                                {{-- tag/transactions icon --}}
                                <template x-if="module.icon === 'tag'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </template>
                                {{-- cube/inventory icon --}}
                                <template x-if="module.icon === 'cube'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </template>
                                {{-- cog/operations icon --}}
                                <template x-if="module.icon === 'cog'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </template>
                                {{-- currency/finance icon --}}
                                <template x-if="module.icon === 'currency'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                                {{-- gear/settings icon --}}
                                <template x-if="module.icon === 'gear'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                    </svg>
                                </template>
                                {{-- building/superadmin icon --}}
                                <template x-if="module.icon === 'building'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </template>
                                {{-- tour_travel icon --}}
                                <template x-if="module.icon === 'tour_travel'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </template>
                                {{-- construction icon --}}
                                <template x-if="module.icon === 'construction'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </template>
                                {{-- cosmetic icon --}}
                                <template x-if="module.icon === 'cosmetic'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </template>
                                {{-- printing icon --}}
                                <template x-if="module.icon === 'printing'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </template>
                                {{-- healthcare icon --}}
                                <template x-if="module.icon === 'healthcare'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </template>
                                {{-- telecom icon --}}
                                <template x-if="module.icon === 'telecom'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                    </svg>
                                </template>
                                {{-- fnb icon --}}
                                <template x-if="module.icon === 'fnb'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </template>
                                {{-- spa icon --}}
                                <template x-if="module.icon === 'spa'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </template>
                                {{-- agriculture icon --}}
                                <template x-if="module.icon === 'agriculture'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </template>
                                {{-- livestock icon --}}
                                <template x-if="module.icon === 'livestock'">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </template>
                                {{-- Fallback icon --}}
                                <template
                                    x-if="!['home','sparkle','tag','cube','cog','currency','gear','building','telecom','fnb','spa','agriculture','livestock','healthcare','tour_travel','construction','cosmetic','printing'].includes(module.icon)">
                                    <svg class="w-6 h-6" :style="`color: var(--module-color-${module.key})`"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </template>
                            </div>

                            {{-- Module name --}}
                            <span class="text-xs font-medium text-gray-700 leading-tight"
                                x-text="module.label"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>{{-- end scrollable body --}}

        {{-- Terakhir Dibuka  sticky footer --}}
        <div x-show="$store.navSystem.recentlyVisited.length > 0"
            class="border-t border-gray-100 px-4 py-3 shrink-0 bg-white rounded-b-2xl">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">
                Terakhir Dibuka
            </p>
            <div class="space-y-1">
                <template x-for="entry in $store.navSystem.recentlyVisited" :key="entry.url">
                    <button @click="window.location.href = entry.url"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 transition text-left">
                        {{-- Module color dot --}}
                        <span class="w-2 h-2 rounded-full shrink-0"
                            :style="`background-color: var(--module-color-${entry.module}, #60a5fa)`"
                            aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-gray-700 truncate" x-text="entry.title"></p>
                            <p class="text-[10px] text-gray-400 truncate capitalize" x-text="entry.module"></p>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
