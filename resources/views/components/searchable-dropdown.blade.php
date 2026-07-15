@props([
    'name',
    'label',
    'selectedId' => '',
    'selectedLabel' => 'Pilih Kota',
    'options' => [],
    'placeholder' => 'Cari bandara atau kota...'
])

<div x-data="{
    open: false,
    search: '',
    selectedId: '{{ $selectedId }}',
    selectedLabel: '{{ $selectedLabel }}',
    options: {{ json_encode($options) }},
    get filtered() {
        if (this.search === '') return this.options;
        return this.options.filter(o => 
            (o.city || '').toLowerCase().includes(this.search.toLowerCase()) ||
            (o.iata_code || '').toLowerCase().includes(this.search.toLowerCase()) ||
            (o.name || '').toLowerCase().includes(this.search.toLowerCase())
        );
    },
    select(opt) {
        this.selectedId = opt.id;
        this.selectedLabel = opt.city + ' (' + opt.iata_code + ')';
        this.open = false;
    }
}" 
class="relative border border-border rounded-xl p-4 flex items-center gap-3 bg-surface hover:border-primary/50 transition-colors cursor-pointer w-full"
@click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
@click.outside="open = false"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId">
    <span class="w-9 h-9 rounded-full bg-neutral text-primary flex items-center justify-center text-base shrink-0">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
    </span>
    <div class="flex-1 min-w-0">
        <div class="text-body-sm text-tertiary mb-0.5">{{ $label }}</div>
        <div class="font-sans font-bold text-on-surface text-body-lg" x-text="selectedLabel">Pilih Kota</div>
    </div>
    
    <!-- Dropdown List -->
    <div x-show="open" 
         x-transition 
         class="absolute left-0 top-full mt-2 bg-surface border border-border/80 rounded-xl shadow-xl z-50 p-3 space-y-3 cursor-default w-[320px] md:w-[380px] lg:w-[400px]"
         @click.stop
    >
        <input 
            type="text" 
            x-ref="searchInput"
            x-model="search"
            placeholder="{{ $placeholder }}"
            class="w-full border border-border/60 rounded-lg px-3 py-2 text-body-sm focus:border-primary focus:ring-1 focus:ring-primary bg-surface"
        >
        <div class="max-h-60 overflow-y-auto divide-y divide-border/40">
            <template x-for="opt in filtered" :key="opt.id">
                <div 
                    @click="select(opt)"
                    class="py-2.5 px-2 hover:bg-primary-tint hover:text-primary rounded-lg cursor-pointer transition-colors flex items-center justify-between text-left"
                >
                    <div>
                        <span class="font-bold block text-on-surface text-body-sm" x-text="opt.city"></span>
                        <span class="text-xs text-tertiary" x-text="opt.name"></span>
                    </div>
                    <span class="bg-neutral text-primary font-mono text-xs px-2 py-1 rounded font-bold" x-text="opt.iata_code"></span>
                </div>
            </template>
            <div x-show="filtered.length === 0" class="text-center py-4 text-body-sm text-tertiary">
                Tidak ditemukan hasil
            </div>
        </div>
    </div>
</div>
