@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 pt-8 pb-16">
    {{-- Hero Banner Slider --}}   
    <div 
        x-data="{ 
            activeSlide: 0, 
            slides: [
                {
                    image: '{{ asset('images/hero.webp') }}',
                    title: 'Terbang Bersama Garuda',
                    description: 'Nikmati pengalaman memesan tiket penerbangan dengan mudah, cepat, dan harga terbaik. Mulai petualanganmu bersama Garuda Indonesia.'
                },
                {
                    image: '{{ asset('images/bali.webp') }}',
                    title: 'Jelajahi Keindahan Bali',
                    description: 'Rasakan kehangatan budaya, pantai eksotis, dan pesona alam Pulau Dewata dengan layanan penerbangan premium terbaik kami.'
                },
                {
                    image: '{{ asset('images/jakarta.webp') }}',
                    title: 'Konektivitas Tanpa Batas',
                    description: 'Terhubung dengan pusat bisnis dan destinasi utama di seluruh penjuru nusantara. Perjalanan dinas dan liburan jadi lebih nyaman.'
                },
                {
                    image: '{{ asset('images/mekah.webp') }}',
                    title: 'Perjalanan Suci Penuh Berkah',
                    description: 'Wujudkan impian ibadah umrah dan haji Anda dengan kenyamanan maksimal dan layanan yang tulus mendampingi langkah Anda.'
                },
                {
                    image: '{{ asset('images/jepang.webp') }}',
                    title: 'Temukan Keajaiban Kyoto',
                    description: 'Jelajahi destinasi internasional impian Anda. Nikmati perpaduan tradisi dan modernitas di Jepang dengan kenyamanan bintang lima.'
                }
            ],
            next() { this.activeSlide = (this.activeSlide + 1) % this.slides.length },
            prev() { this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length },
            init() { setInterval(() => this.next(), 8000) }
        }"
        class="relative rounded-2xl overflow-hidden mb-12 shadow-lg h-[550px] sm:h-[500px] bg-secondary group"
    >
        <!-- Slides -->
        <template x-for="(slide, index) in slides" :key="index">
            <div 
                class="absolute inset-0 w-full h-full"
                :style="'transform: translateX(' + ((index - activeSlide) * 100) + '%); transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1); z-index: ' + (activeSlide === index ? 20 : 10) + ';'"
            >
                <!-- Background Image -->
                <img
                    :src="slide.image"
                    :alt="slide.title"
                    class="absolute inset-0 w-full h-full object-cover opacity-60"
                >
                <!-- Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-secondary via-secondary/70 to-transparent"></div>
                
                <!-- Content -->
                <div class="absolute inset-0 flex items-center">
                    <div class="max-w-2xl space-y-4 sm:space-y-6 px-6 sm:px-12 md:px-20 w-full z-20 pb-20 md:pb-0">
                        <h1 
                            x-text="slide.title"
                            class="font-sans font-extrabold text-3xl sm:text-4xl md:text-5xl text-surface leading-tight tracking-tight drop-shadow-md"
                        ></h1>
                        <p 
                            x-text="slide.description"
                            class="text-base sm:text-lg text-surface/90 max-w-xl font-medium drop-shadow-sm"
                        ></p>
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-2">
                            @auth
                                <x-button variant="primary" href="{{ route('flights') }}" size="lg" class="w-full sm:w-auto justify-center" wire:navigate>Cari Penerbangan</x-button>
                            @else
                                <x-button variant="accent" href="{{ route('login') }}" size="lg" class="w-full sm:w-auto justify-center" wire:navigate>Masuk</x-button>
                                <x-button variant="secondary" href="{{ route('register') }}" size="lg" class="w-full sm:w-auto justify-center bg-surface/10 border-surface text-surface hover:bg-surface hover:text-primary backdrop-blur-sm" wire:navigate>Daftar</x-button>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Navigation Buttons -->
        <button 
            @click="prev()" 
            class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-surface/20 hover:bg-surface/40 text-surface items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition duration-300 z-30"
        >
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button 
            @click="next()" 
            class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-surface/20 hover:bg-surface/40 text-surface items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition duration-300 z-30"
        >
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
        </button>

        <!-- Indicators -->
        <div class="absolute left-1/2 -translate-x-1/2 flex gap-2 z-30 bottom-24 md:bottom-[100px]">
            <template x-for="(slide, index) in slides" :key="index">
                <button 
                    @click="activeSlide = index" 
                    class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                    :class="activeSlide === index ? 'bg-surface w-8' : 'bg-surface/50'"
                ></button>
            </template>
        </div>
    </div>

    {{-- Flight Search --}}
    <form
        method="GET"
        action="{{ route('flights') }}"
        class="bg-surface border border-border/80 rounded-2xl p-6 mb-16 shadow-xl -mt-20 relative z-20 max-w-6xl mx-auto"
    >
        <h2 class="font-sans font-extrabold text-2xl text-primary mb-6">Cari Penerbangan</h2>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            
            <!-- Keberangkatan Custom Dropdown -->
            <x-searchable-dropdown 
                name="departure_id" 
                label="Keberangkatan" 
                :selected-id="request('departure_id')"
                :selected-label="request('departure_id') ? $airports->find(request('departure_id'))?->city . ' (' . $airports->find(request('departure_id'))?->iata_code . ')' : 'Pilih Kota'"
                :options="$airports"
            />

            <!-- Tujuan Custom Dropdown -->
            <x-searchable-dropdown 
                name="arrival_id" 
                label="Tujuan" 
                :selected-id="request('arrival_id')"
                :selected-label="request('arrival_id') ? $airports->find(request('arrival_id'))?->city . ' (' . $airports->find(request('arrival_id'))?->iata_code . ')' : 'Pilih Kota'"
                :options="$airports"
            />

            <!-- Tanggal -->
            <x-date-picker 
                name="date" 
                label="Tanggal" 
                :value="request('date')"
            />

            <!-- Penumpang -->
            <div class="border border-border rounded-lg p-4 flex items-center gap-3 bg-surface hover:border-primary/50 transition-colors">
                <span class="w-9 h-9 rounded-full bg-neutral text-primary flex items-center justify-center text-base shrink-0">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-body-sm text-tertiary mb-0.5">Penumpang</div>
                    <input
                        type="number"
                        min="1"
                        name="passengers"
                        value="1"
                        class="w-full border-0 p-0 font-sans font-semibold text-on-surface focus:ring-0 text-body-lg bg-transparent"
                    >
                </div>
            </div>

            <div class="flex items-center">
                <button
                    type="submit"
                    class="w-full bg-accent hover:bg-accent-hover text-surface rounded-full py-4 font-sans font-bold transition flex items-center justify-center gap-2 shadow-sm duration-200"
                >
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Cari
                </button>
            </div>
        </div>
    </form>

    {{-- Feature Highlights --}}
    <section class="mb-16">
        <h2 class="font-display text-headline-lg text-primary text-center mb-10">Mengapa Memilih Garuda?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-card class="bg-surface text-center">
                <div class="flex flex-col items-center gap-4 p-2">
                    <span class="w-12 h-12 rounded-full bg-neutral text-on-surface flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <h3 class="font-display text-headline-md text-primary">Pemesanan Mudah</h3>
                    <p class="text-body-md text-tertiary">Proses pemesanan tiket yang cepat dan intuitif dalam hitungan menit.</p>
                </div>
            </x-card>

            <x-card class="bg-surface text-center">
                <div class="flex flex-col items-center gap-4 p-2">
                    <span class="w-12 h-12 rounded-full bg-neutral text-on-surface flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 11h18M3 11l3-7m-3 7l3 7m15-7l-3-7m3 7l-3 7m-6-2V9m0 0l-3 3m3-3l3 3"/></svg>
                    </span>
                    <h3 class="font-display text-headline-md text-primary">Banyak Pilihan</h3>
                    <p class="text-body-md text-tertiary">Ratusan rute domestik dan internasional dengan berbagai maskapai terbaik.</p>
                </div>
            </x-card>

            <x-card class="bg-surface text-center">
                <div class="flex flex-col items-center gap-4 p-2">
                    <span class="w-12 h-12 rounded-full bg-neutral text-on-surface flex items-center justify-center">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </span>
                    <h3 class="font-display text-headline-md text-primary">Pembayaran Aman</h3>
                    <p class="text-body-md text-tertiary">Transaksi terenkripsi dengan berbagai metode pembayaran terpercaya.</p>
                </div>
            </x-card>
        </div>
    </section>

    {{-- Popular Destinations --}}
    <section class="mb-16">
        <h2 class="font-display text-headline-lg text-primary mb-2">Destinasi Populer</h2>
        <p class="text-body-md text-tertiary mb-8">Destinasi paling diminati penumpang kami tahun ini.</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @php
                $destinations = [
                    ['city' => 'Kyoto', 'image' => 'images/62036263-5534-4187-aae9-e92e03867a34.jpg'],
                    ['city' => 'Bali', 'image' => 'images/e0d6cbc6be0aa240b35b2d50e55074db.jpg'],
                    ['city' => 'Jakarta', 'image' => 'images/monas.jpg'],
                    ['city' => 'Osaka', 'image' => 'images/40360.jpg'],
                    ['city' => 'Mecca', 'image' => 'images/al-masjid-al-haram-3-scaled.jpg'],
                ];
            @endphp

            @foreach($destinations as $destination)
                <a href="{{ route('flights') }}"
                   class="relative rounded-lg overflow-hidden aspect-[3/4] shadow-md group cursor-pointer block">
                    <img
                        src="{{ asset($destination['image']) }}"
                        alt="{{ $destination['city'] }}"
                        class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-transparent"></div>
                    <div class="absolute bottom-4 left-4 text-surface font-display font-bold text-headline-sm">
                        {{ $destination['city'] }}
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</div>

{{-- Sections outside container for full-width backgrounds --}}
@include('dashboard.sections.hotels')
@include('dashboard.sections.schedule')
@include('dashboard.sections.testimonial')
@include('dashboard.sections.call-us')

{{-- Footer --}}
<footer class="bg-secondary text-surface py-8 px-6 text-center">
    <p class="text-body-sm opacity-70">&copy; {{ date('Y') }} Garuda Indonesia. Semua hak dilindungi.</p>
</footer>
@endsection

@push('scripts')
@endpush
