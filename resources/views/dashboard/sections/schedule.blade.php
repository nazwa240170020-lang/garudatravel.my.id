@php
    $section = $sections['schedule'] ?? null;
    $items = $section?->data['items'] ?? [];
@endphp

@if ($section && count($items))
<section id="schedule" class="py-20 px-6 lg:px-12 bg-neutral/50 border-t border-b border-border/40">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-12">
            <span class="text-xs font-bold tracking-widest text-primary uppercase">Flight Schedule</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mt-2">
                {{ $section->title }}
            </h2>
            <p class="text-slate-500 mt-3 max-w-xl mx-auto">
                {{ $section->subtitle }}
            </p>
        </div>

        <div class="space-y-4">
            @foreach($items as $s)
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden">
                    <div class="p-6 flex justify-between items-center flex-wrap gap-4">

                        <div class="min-w-[140px]">
                            @if($s['logo'] ?? null)
                                <img src="{{ asset('storage/' . $s['logo']) }}"
                                    alt="{{ $s['airline'] }}"
                                    class="h-8 object-contain mb-1">
                            @endif
                            <p class="font-bold text-base text-slate-900">{{ $s['airline'] }}</p>
                            <p class="text-gray-400 text-xs">{{ $s['depart'] }} – {{ $s['arrive'] }}</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-center">
                                <div class="font-bold text-xl text-slate-900">{{ $s['from'] }}</div>
                                <div class="text-gray-400 text-xs">{{ $s['from_city'] }}</div>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs text-gray-400">{{ $s['duration'] }}</span>
                                <div class="flex items-center gap-1 text-gray-300">
                                    <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                    <div class="w-10 h-px bg-gray-300"></div>
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-gray-300"><path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                                    <div class="w-10 h-px bg-gray-300"></div>
                                    <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ $s['transit'] === 0 ? 'Direct Flight' : 'Transit '.$s['transit'].'x' }}
                                </span>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-xl text-slate-900">{{ $s['to'] }}</div>
                                <div class="text-gray-400 text-xs">{{ $s['to_city'] }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right min-w-[120px]">
                                <p class="text-xs text-gray-400">Starts from</p>
                                <div class="font-bold text-primary text-xl">
                                    Rp {{ number_format($s['price'], 0, ',', '.') }}
                                </div>
                            </div>
                            <a href="{{ route('flights') }}"
                               class="inline-block bg-primary hover:bg-primary-hover text-white font-semibold rounded-full px-6 py-2.5 transition text-sm shadow-sm">
                                Book Now
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif
