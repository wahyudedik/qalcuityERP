


<aside id="module-sidebar" x-data x-show="$store.navSystem.sidebarVisible"
    :class="$store.navSystem.sidebarCollapsed ? 'w-0' : 'w-[260px]'"
    class="shrink-0
           fixed top-14 left-0 z-40
           h-[calc(100vh-3.5rem)]"
    style="transition: width 250ms cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden;" role="navigation"
    :aria-label="`Navigasi ${NAV_GROUPS[$store.navSystem.activeModule]?.title ?? ''}`">

    
    <div class="w-[260px] h-full flex flex-col bg-white border-r border-gray-200"
        :style="$store.navSystem.sidebarCollapsed ? 'opacity:0;pointer-events:none' :
            'opacity:1;transition:opacity 200ms ease'">

        
        <div class="flex items-center justify-between h-12 px-4 border-b border-gray-200 shrink-0">
            
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0"
                    :style="`background-color: color-mix(in srgb, var(--module-color-${$store.navSystem.activeModule}) 15%, transparent)`">
                    <svg class="w-3.5 h-3.5" :style="`color: var(--module-color-${$store.navSystem.activeModule})`"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-500 truncate"
                    x-text="NAV_GROUPS[$store.navSystem.activeModule]?.title ?? ''"></span>
            </div>

            
            <button @click="$store.navSystem.toggleCollapse()"
                class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 transition shrink-0" title="Sembunyikan sidebar"
                aria-label="Sembunyikan sidebar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        
        <div class="px-3 py-2 border-b border-gray-100 shrink-0">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" placeholder="Cari menu..." x-model="$store.navSystem.sidebarQuery"
                    aria-label="Cari menu navigasi"
                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg bg-gray-100
                           border border-transparent focus:border-gray-300
                           text-gray-700 placeholder-gray-400 outline-none transition">
            </div>
        </div>

        
        <nav class="flex-1 overflow-y-auto py-2 px-2 scrollbar-thin" aria-label="Menu navigasi" x-data="{
            collapsedSections: {},
            initSections() {
                // Auto-expand section that contains active item
                const items = $store.navSystem.filteredNavItems;
                let currentSection = null;
                items.forEach(item => {
                    if (item.section) {
                        currentSection = item.section;
                        // Default: collapse all sections except the one with active item
                        if (this.collapsedSections[currentSection] === undefined) {
                            this.collapsedSections[currentSection] = true;
                        }
                    } else if (item.active && currentSection) {
                        this.collapsedSections[currentSection] = false;
                    }
                });
                // If no section has active item, expand first section
                const sections = items.filter(i => i.section).map(i => i.section);
                const hasExpanded = sections.some(s => !this.collapsedSections[s]);
                if (!hasExpanded && sections.length > 0) {
                    this.collapsedSections[sections[0]] = false;
                }
            },
            toggleSection(name) {
                this.collapsedSections[name] = !this.collapsedSections[name];
            },
            isSectionCollapsed(name) {
                return this.collapsedSections[name] ?? false;
            },
            getSectionForIndex(index) {
                const items = $store.navSystem.filteredNavItems;
                let section = null;
                for (let i = index - 1; i >= 0; i--) {
                    if (items[i].section) { section = items[i].section; break; }
                }
                return section;
            },
            isItemVisible(index) {
                // If searching, show all items
                if ($store.navSystem.sidebarQuery) return true;
                const section = this.getSectionForIndex(index);
                if (!section) return true;
                return !this.isSectionCollapsed(section);
            }
        }"
            x-init="$nextTick(() => initSections())">
            <template x-for="(item, index) in $store.navSystem.filteredNavItems" :key="`nav-item-${index}`">
                <div>
                    
                    <button x-show="item.section" @click="toggleSection(item.section)"
                        class="w-full flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-gray-400 hover:text-gray-600 px-3 pt-4 pb-1.5 mt-1 border-t border-gray-100 cursor-pointer transition-colors duration-150 group"
                        :aria-expanded="!isSectionCollapsed(item.section)" style="display: none;">
                        <span x-text="item.section"></span>
                        <svg class="w-3 h-3 transition-transform duration-200 text-gray-300 group-hover:text-gray-500"
                            :class="isSectionCollapsed(item.section) ? '-rotate-90' : 'rotate-0'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    
                    <a x-show="!item.section && isItemVisible(index)" :href="item.href === '#logout' ? '#' : item.href"
                        :class="item.active ?
                            'font-semibold' :
                            'text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                        :style="item.active ?
                            `color: var(--module-color-${$store.navSystem.activeModule}); background-color: color-mix(in srgb, var(--module-color-${$store.navSystem.activeModule}) 10%, transparent)` :
                            (item.danger ? 'color: #f87171' : '')"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-all duration-150 relative"
                        @click="item.href === '#logout' ? (event.preventDefault(), document.getElementById('logout-form').submit()) : null"
                        style="display: none;" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        
                        <span x-show="item.active" class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-4 rounded-r"
                            :style="`background-color: var(--module-color-${$store.navSystem.activeModule})`"
                            aria-hidden="true"></span>

                        <span class="truncate" x-text="item.label"></span>

                        
                        <span x-show="item.badge && item.badge !== 'null'" x-text="item.badge"
                            :class="item.badgeClass === 'badge-red' ?
                                'bg-red-100 text-red-600' :
                                'bg-amber-100 text-amber-600'"
                            class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"></span>
                    </a>
                </div>
            </template>

            
            <p x-show="$store.navSystem.sidebarQuery && $store.navSystem.filteredNavItems.filter(i => !i.section).length === 0"
                class="text-xs text-gray-400 text-center py-4 px-3">
                Tidak ada menu yang cocok
            </p>
        </nav>
    </div>
</aside>



<div x-data x-show="$store.navSystem.sidebarVisible && !$store.navSystem.sidebarCollapsed"
    @click="$store.navSystem.closeSidebar()" class="fixed inset-0 z-30 bg-black/40 lg:hidden"
    x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;" aria-hidden="true">
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/layouts/_nav_sidebar.blade.php ENDPATH**/ ?>