@php
    $section = $sections['call-us'] ?? null;
    $data = $section?->data ?? [];
@endphp

@if ($section)
<section id="call-us" class="px-6 lg:px-12 py-20 bg-neutral/50 border-t border-b border-border/40">
    <div class="max-w-6xl mx-auto">

        <div class="flex flex-col lg:flex-row items-center justify-between gap-10">

            <div class="max-w-md text-center lg:text-left">
                <span class="text-xs font-bold tracking-widest text-primary uppercase">Butuh Bantuan?</span>
                <h2 class="mt-2 text-3xl lg:text-[40px] leading-tight font-extrabold text-slate-900">
                    {{ $section->title }}
                </h2>
                <p class="mt-4 text-slate-500 text-sm lg:text-base">
                    {{ $section->subtitle }}
                </p>
                <a href="tel:{{ $data['phone'] ?? '0800' }}"
                   class="inline-flex items-center gap-2 mt-6 bg-accent hover:bg-accent-hover text-surface font-semibold rounded-full px-7 py-3 text-sm transition shadow-sm">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg> Hubungi Sekarang
                </a>
            </div>

            <div class="w-full lg:w-auto divide-y divide-slate-200 lg:divide-y-0 lg:flex lg:gap-10 lg:divide-x">

                <div class="py-4 lg:py-0 lg:pl-10 first:lg:pl-0 text-center lg:text-left">
                    <p class="text-xs text-gray-400 font-medium mb-1">Call Center</p>
                    <p class="font-bold text-slate-900 text-base">{{ $data['phone'] ?? '0804-1-807-807' }}</p>
                </div>

                <div class="py-4 lg:py-0 lg:pl-10 text-center lg:text-left">
                    <p class="text-xs text-gray-400 font-medium mb-1">Email</p>
                    <p class="font-bold text-slate-900 text-base">{{ $data['email'] ?? 'admin@garuda.com' }}</p>
                </div>

                <div class="py-4 lg:py-0 lg:pl-10 text-center lg:text-left">
                    <p class="text-xs text-gray-400 font-medium mb-1">Jam Operasional</p>
                    <p class="font-bold text-slate-900 text-base">{{ $data['hours'] ?? '24 Jam Setiap Hari' }}</p>
                </div>

            </div>

        </div>

    </div>
</section>
@endif
