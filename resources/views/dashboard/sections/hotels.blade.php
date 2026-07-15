@php
    $section = $sections['hotels'] ?? null;
    $items = $section?->data['items'] ?? [];
@endphp

@if ($section && count($items))
<section id="hotels" class="py-20 px-6 lg:px-12 bg-neutral/50 border-t border-b border-border/40">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-12">
            <span class="text-xs font-bold tracking-widest text-primary uppercase">Partner Hotels</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mt-2">
                {{ $section->title }}
            </h2>
            <p class="text-slate-500 mt-3 max-w-xl mx-auto">
                {{ $section->subtitle }}
            </p>
        </div>

        <div class="space-y-4">
            @foreach($items as $hotel)
            <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
                <div class="p-4 flex items-center gap-5 flex-wrap">

                    <div class="w-full sm:w-32 h-32 rounded-2xl overflow-hidden shrink-0">
                        <img src="{{ $hotel['image'] }}" alt="{{ $hotel['name'] }}" class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1 min-w-[160px]">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-slate-900">{{ $hotel['name'] }}</h3>
                            <span class="flex items-center gap-1 text-xs font-semibold text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full shrink-0">
                                ★ {{ $hotel['rating'] }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-400">{{ $hotel['city'] }}</p>
                    </div>

                    <div class="flex items-center gap-4 ml-auto">
                        <div class="text-right min-w-[120px]">
                            <p class="text-xs text-gray-400">Starts from</p>
                            <div class="font-bold text-primary text-xl">
                                Rp {{ number_format($hotel['price'], 0, ',', '.') }}
                                <span class="text-xs font-normal text-gray-400">/malam</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif
