@php
    $section = $sections['testimonial'] ?? null;
    $items = $section?->data['items'] ?? [];
@endphp

@if ($section && count($items))
<section id="testimonial" class="py-20 px-6 lg:px-12 bg-neutral/50 border-t border-b border-border/40">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-12">
            <span class="text-xs font-bold tracking-widest text-primary uppercase">Testimonial</span>
            <h2 class="text-3xl lg:text-4xl font-extrabold text-slate-900 mt-2">
                {{ $section->title }}
            </h2>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                {{ $section->subtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($items as $t)
            <div class="bg-white rounded-3xl p-6 shadow-lg">
                <div class="flex gap-0.5 text-amber-400 mb-3 text-sm">
                    @for($i = 0; $i < 5; $i++)
                        {{ $i < $t['rating'] ? '★' : '☆' }}
                    @endfor
                </div>
                <p class="text-sm text-gray-600 mb-5 leading-relaxed">
                    "{{ $t['message'] }}"
                </p>
                <div class="flex items-center gap-3">
                    <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}" class="w-10 h-10 rounded-full object-cover">
                    <div>
                        <p class="font-bold text-sm text-gray-900">{{ $t['name'] }}</p>
                        <p class="text-xs text-gray-400">{{ $t['role'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</section>
@endif
