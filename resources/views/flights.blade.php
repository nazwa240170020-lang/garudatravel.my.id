@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10 space-y-6">

    @if(request()->filled('departure_id') || request()->filled('date'))
        {{-- Summary bar --}}
        <x-card padding="6">
            <div class="flex gap-8 items-center flex-wrap">
                <div>
                    <p class="text-label-sm text-tertiary">Keberangkatan</p>
                    <p class="font-display text-headline-sm text-primary">{{ $airports->find(request('departure_id'))?->iata_code ?? '-' }}</p>
                </div>
                <svg class="w-5 h-5 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
                <div>
                    <p class="text-label-sm text-tertiary">Tujuan</p>
                    <p class="font-display text-headline-sm text-primary">{{ $airports->find(request('arrival_id'))?->iata_code ?? '-' }}</p>
                </div>
                <div class="w-px h-8 bg-border hidden sm:block"></div>
                <div>
                    <p class="text-label-sm text-tertiary">Tanggal</p>
                    <p class="font-display text-headline-sm text-primary">{{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('Y-m-d') : '-' }}</p>
                </div>
                <div class="w-px h-8 bg-border hidden sm:block"></div>
                <div>
                    <p class="text-label-sm text-tertiary">Penumpang</p>
                    <p class="font-display text-headline-sm text-primary">{{ request('passengers', 1) }} orang</p>
                </div>
                <x-button variant="secondary" href="{{ route('flights') }}" class="ml-auto">Ubah Pencarian</x-button>
            </div>
        </x-card>
    @else
        {{-- Search form --}}
        <x-card padding="6">
            <form method="GET" action="{{ route('flights') }}" class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-end">
                <div class="lg:col-span-1">
                    <x-searchable-dropdown 
                        name="departure_id" 
                        label="Dari" 
                        :selected-id="request('departure_id')"
                        :selected-label="request('departure_id') ? $airports->find(request('departure_id'))?->city . ' (' . $airports->find(request('departure_id'))?->iata_code . ')' : 'Pilih Kota'"
                        :options="$airports"
                    />
                </div>
                <div class="lg:col-span-1">
                    <x-searchable-dropdown 
                        name="arrival_id" 
                        label="Ke" 
                        :selected-id="request('arrival_id')"
                        :selected-label="request('arrival_id') ? $airports->find(request('arrival_id'))?->city . ' (' . $airports->find(request('arrival_id'))?->iata_code . ')' : 'Pilih Kota'"
                        :options="$airports"
                    />
                </div>
                <div class="lg:col-span-1">
                    <x-date-picker 
                        name="date" 
                        label="Tanggal" 
                        :value="request('date')"
                    />
                </div>
                <div class="border border-border rounded-xl p-4 flex items-center gap-3 bg-surface hover:border-primary/50 transition-colors lg:col-span-1">
                    <span class="w-9 h-9 rounded-full bg-neutral text-primary flex items-center justify-center text-base shrink-0">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-body-sm text-tertiary mb-0.5">Penumpang</div>
                        <input type="number" name="passengers" value="{{ request('passengers', 1) }}" min="1"
                            class="w-full border-0 p-0 font-sans font-semibold text-on-surface focus:ring-0 text-body-lg bg-transparent">
                    </div>
                </div>
                <div class="flex items-center lg:col-span-1">
                    <button
                        type="submit"
                        class="w-full bg-accent hover:bg-accent-hover text-surface rounded-full py-4 font-sans font-bold transition flex items-center justify-center gap-2 shadow-sm duration-200"
                    >
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Cari
                    </button>
                </div>
            </form>
        </x-card>
    @endif

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        {{-- Filter Sidebar --}}
        <form method="GET" action="{{ route('flights') }}" id="filter-form" class="w-full lg:w-64 shrink-0">
            <input type="hidden" name="departure_id" value="{{ request('departure_id') }}">
            <input type="hidden" name="arrival_id"   value="{{ request('arrival_id') }}">
            <input type="hidden" name="date"         value="{{ request('date') }}">
            <input type="hidden" name="passengers"   value="{{ request('passengers', 1) }}">

            <x-card padding="6" class="space-y-5">
                <h3 class="font-display text-headline-sm text-primary">Filter</h3>

                <div>
                    <p class="text-label-md text-primary mb-2">Tipe Penerbangan</p>
                    @foreach(['direct' => 'Langsung', 'transit_1x' => 'Transit 1x', 'transit_2x' => 'Transit 2x'] as $val => $label)
                        <label class="flex items-center gap-2 text-body-sm py-1 text-on-surface hover:text-primary transition-colors">
                            <input type="checkbox" name="transit_type[]" value="{{ $val }}"
                                {{ in_array($val, request('transit_type', [])) ? 'checked' : '' }}
                                class="rounded border-border text-primary focus:ring-primary w-4 h-4"
                                onchange="document.getElementById('filter-form').submit()">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <hr class="border-border">

                <div>
                    <p class="text-label-md text-primary mb-2">Maskapai</p>
                    @foreach($airlines as $airline)
                        <label class="flex items-center gap-3 py-2 text-on-surface hover:text-primary transition-colors">
                            <input type="checkbox" name="airline_id[]" value="{{ $airline->id }}"
                                {{ in_array($airline->id, request('airline_id', [])) ? 'checked' : '' }}
                                class="rounded border-border text-primary focus:ring-primary w-4 h-4 shrink-0"
                                onchange="document.getElementById('filter-form').submit()">
                            <img src="{{ $airline->logo_url }}" alt="{{ $airline->name }}" class="h-6 w-12 object-contain shrink-0">
                            <div class="text-body-sm">
                                <div class="font-medium text-primary">{{ $airline->name }}</div>
                                <div class="text-label-sm text-tertiary">Tersedia</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <hr class="border-border">

                <div>
                    <p class="text-label-md text-primary mb-2">Fasilitas</p>
                    @foreach($facilities as $facility)
                        <label class="flex items-center gap-2 text-body-sm py-1 text-on-surface hover:text-primary transition-colors">
                            <input type="checkbox" name="facility_id[]" value="{{ $facility->id }}"
                                {{ in_array($facility->id, request('facility_id', [])) ? 'checked' : '' }}
                                class="rounded border-border text-primary focus:ring-primary w-4 h-4"
                                onchange="document.getElementById('filter-form').submit()">
                            <img src="{{ $facility->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($facility->image) ? asset('storage/' . $facility->image) : asset('images/logo.svg') }}" alt="{{ $facility->name }}" class="w-4 h-4 object-contain">
                            <span>{{ $facility->name }}</span>
                        </label>
                    @endforeach
                </div>

                @if(request()->hasAny(['transit_type', 'airline_id', 'facility_id']))
                    <div class="mt-2">
                        <a href="{{ route('flights', request()->only(['departure_id','arrival_id','date','passengers'])) }}"
                            class="text-label-sm text-secondary hover:text-tertiary transition-colors">Reset Filter</a>
                    </div>
                @endif
            </x-card>
        </form>

        {{-- Results --}}
        <div class="w-full lg:flex-1 space-y-4">

            @if(is_null($flights))
                <x-card padding="8" class="text-center">
                    <p class="text-body-md text-tertiary">Pilih rute dan tanggal, lalu klik Cari untuk melihat penerbangan tersedia.</p>
                </x-card>

            @elseif($flights->isEmpty())
                <x-card padding="8" class="text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <p class="text-body-md text-tertiary">Tidak ada penerbangan yang ditemukan untuk rute ini.</p>
                </x-card>

            @else
                <p class="text-body-sm text-secondary font-medium">{{ $flights->count() }} penerbangan ditemukan</p>

                @foreach($flights as $flight)
                    @php
                        $segments     = $flight->segments->sortBy('sequence')->values();
                        $firstSegment = $segments->first();
                        $lastSegment  = $segments->last();
                        $transitCount = max($segments->count() - 2, 0);

                        $durationMinutes = null;
                        if ($firstSegment?->time && $lastSegment?->time) {
                            $durationMinutes = \Carbon\Carbon::parse($firstSegment->time)
                                ->diffInMinutes(\Carbon\Carbon::parse($lastSegment->time));
                        }
                    @endphp

                    <x-card padding="6" class="overflow-hidden" x-data="{ open: false }">

                        {{-- Route summary --}}
                        <div class="flex justify-between items-center flex-wrap gap-4 select-none"
                             @click="open = !open">
                            <div class="min-w-[140px]">
                            <img src="{{ $flight->airline->logo_url }}"
                                alt="{{ $flight->airline->name }}"
                                class="h-8 object-contain mb-1">
                                <p class="font-display text-headline-sm text-primary">{{ $flight->airline?->name ?? '-' }}</p>
                                <p class="text-label-sm text-tertiary">
                                    {{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('H:i') : '-' }}
                                    &ndash;
                                    {{ $lastSegment?->time ? \Carbon\Carbon::parse($lastSegment->time)->format('H:i') : '-' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <div class="font-display text-headline-md text-primary">{{ $firstSegment?->airport?->iata_code }}</div>
                                    <div class="text-label-sm text-tertiary">{{ $firstSegment?->airport?->city }}</div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    @if($durationMinutes)
                                        <span class="text-label-sm text-tertiary">{{ intdiv($durationMinutes, 60) }}j {{ $durationMinutes % 60 }}m</span>
                                    @endif
                                    <div class="flex items-center gap-1 text-border">
                                        <div class="w-2 h-2 rounded-full bg-border"></div>
                                        <div class="w-10 h-px bg-border"></div>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                        <div class="w-10 h-px bg-border"></div>
                                        <div class="w-2 h-2 rounded-full bg-border"></div>
                                    </div>
                                    <span class="text-label-sm text-tertiary">{{ $transitCount === 0 ? 'Langsung' : 'Transit '.$transitCount.'x' }}</span>
                                </div>
                                <div class="text-center">
                                    <div class="font-display text-headline-md text-primary">{{ $lastSegment?->airport?->iata_code }}</div>
                                    <div class="text-label-sm text-tertiary">{{ $lastSegment?->airport?->city }}</div>
                                </div>
                            </div>

                            <div class="text-tertiary transition-transform duration-300" :class="{ 'rotate-180': open }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Class tiers inline --}}
                        <div class="mt-4 grid sm:grid-cols-2 gap-4">
                            @foreach($flight->classes as $class)
                                <div class="border border-border rounded-lg p-5 flex flex-col bg-neutral/30 hover:border-primary/30 transition-all">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-display text-headline-sm text-primary">{{ ucfirst($class->class_type) }} Class</span>
                                        @if($class->class_type === 'business')
                                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        @endif
                                    </div>

                                    @php $facilties = $class->facilties ?? collect(); @endphp
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        @foreach($facilties as $facility)
                                            <x-chip>
                                                <img src="{{ $facility->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($facility->image) ? asset('storage/' . $facility->image) : asset('images/logo.svg') }}" alt="" class="w-3 h-3 object-contain">
                                                {{ $facility->name }}
                                            </x-chip>
                                        @endforeach
                                        <x-chip>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $class->total_seats }} kursi
                                        </x-chip>
                                    </div>

                                    <div class="flex items-center justify-between mt-auto">
                                        <div>
                                            <span class="text-label-sm text-tertiary">per orang</span>
                                            <p class="font-display text-headline-md text-primary">Rp {{ number_format($class->price, 0, ',', '.') }}</p>
                                        </div>
                                        <x-button variant="primary" size="lg" href="{{ route('booking.choose-seat', ['flight' => $flight->flight_number, 'flightClass' => $class->id, 'passengers' => request('passengers', 1)]) }}">
                                            Pilih
                                        </x-button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Expandable segment details --}}
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2"
                             class="border-t border-border mt-6 pt-6">
                            <p class="text-label-md text-primary mb-4">Detail Rute</p>
                            @foreach($segments as $segment)
                                <div class="flex items-start gap-3">
                                    <div class="text-right w-16 shrink-0 pt-1">
                                        <div class="font-display text-headline-sm text-secondary">{{ $segment->time ? \Carbon\Carbon::parse($segment->time)->format('H:i') : '-' }}</div>
                                        <div class="text-label-sm text-tertiary">{{ $segment->time ? \Carbon\Carbon::parse($segment->time)->format('d M Y') : '' }}</div>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-surface text-label-sm shrink-0 {{ $loop->first || $loop->last ? 'bg-primary' : 'bg-tertiary' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-px h-8 border-l-2 border-dashed border-border my-1"></div>
                                        @endif
                                    </div>
                                    <div class="pb-2 pt-1">
                                        <div class="text-label-sm font-semibold uppercase tracking-wide {{ $loop->first ? 'text-primary' : ($loop->last ? 'text-primary' : 'text-tertiary') }}">
                                            {{ $loop->first ? 'Keberangkatan' : ($loop->last ? 'Kedatangan' : 'Transit') }}
                                        </div>
                                        <div class="font-display text-headline-sm text-primary">
                                            {{ $segment->airport?->name }}
                                            <span class="text-tertiary font-normal">({{ $segment->airport?->iata_code }})</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </x-card>
                @endforeach

            @endif

        </div>
    </div>
</div>
@endsection
