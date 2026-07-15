@extends('layouts.app')

@section('content')
@php
    $segments     = $flight->segments->sortBy('sequence')->values();
    $firstSegment = $segments->first();
    $lastSegment  = $segments->last();
    $transitCount = max($segments->count() - 2, 0);
    $lowestPrice  = $flight->classes->min('price');

    $durationMinutes = null;
    if ($firstSegment?->time && $lastSegment?->time) {
        $durationMinutes = \Carbon\Carbon::parse($firstSegment->time)
            ->diffInMinutes(\Carbon\Carbon::parse($lastSegment->time));
    }
@endphp

<div class="max-w-6xl mx-auto px-6 py-10">

    <x-button variant="secondary" href="{{ route('flights') }}" class="mb-6">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Cari Penerbangan
    </x-button>

    <h1 class="font-sans font-extrabold text-3xl text-primary mb-6">Pilih Kelas</h1>

    <div class="flex gap-6 items-start flex-wrap lg:flex-nowrap">

        <!-- ===== KARTU "YOUR FLIGHT" ===== -->
        <x-card class="w-full lg:w-80 shrink-0 space-y-4">
            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-400 font-medium">Keberangkatan</p>
                    <p class="font-bold text-on-surface">
                        {{ $firstSegment?->airport?->name }}
                        ({{ $firstSegment?->airport?->iata_code }})
                    </p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-400 font-medium">Kedatangan</p>
                <p class="font-bold text-on-surface">
                    {{ $lastSegment?->airport?->name }}
                    ({{ $lastSegment?->airport?->iata_code }})
                </p>
            </div>

            <div class="flex justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-400 font-medium">Tanggal</p>
                    <p class="font-bold text-on-surface">
                        {{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('d F Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Jumlah</p>
                    <p class="font-bold text-on-surface">{{ $passengers }} Orang</p>
                </div>
            </div>

            <hr class="border-border">

            <div class="flex items-center gap-3">
                <img src="{{ $flight->airline->logo_url }}"
                    alt="{{ $flight->airline->name }}"
                    class="h-6 object-contain">
                <div>
                    <p class="font-bold text-sm text-primary">{{ $flight->airline?->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('H:i') : '-' }}
                        -
                        {{ $lastSegment?->time ? \Carbon\Carbon::parse($lastSegment->time)->format('H:i') : '-' }}
                    </p>
                </div>
            </div>

            @if($durationMinutes)
                <p class="text-xs text-gray-400">
                    {{ intdiv($durationMinutes, 60) }} Jam {{ $durationMinutes % 60 }} Menit
                </p>
            @endif

            <div class="flex justify-between items-center">
                <p class="font-medium text-sm text-on-surface">
                    {{ $firstSegment?->airport?->iata_code }}
                    <span class="text-gray-300 mx-1">→</span>
                    {{ $lastSegment?->airport?->iata_code }}
                </p>
                <p class="font-bold text-primary">
                    Rp {{ number_format($lowestPrice, 0, ',', '.') }}
                </p>
            </div>

            <p class="text-xs text-gray-400">
                {{ $transitCount === 0 ? 'Langsung' : 'Transit '.$transitCount.'x' }}
            </p>

            {{-- Detail rute lengkap --}}
            <details class="text-sm">
                <summary class="cursor-pointer text-primary font-medium focus:outline-none">Detail Rute</summary>
                <div class="mt-3 space-y-2">
                    @foreach($segments as $segment)
                        <div class="text-xs">
                            <span class="font-bold text-primary">
                                {{ $segment->time ? \Carbon\Carbon::parse($segment->time)->format('H:i, d M Y') : '-' }}
                            </span>
                            —
                            <span class="font-semibold text-on-surface">{{ $segment->airport?->name }}</span>
                            ({{ $segment->airport?->iata_code }})
                            <span class="text-gray-400">
                                {{ $loop->first ? '(Keberangkatan)' : ($loop->last ? '(Kedatangan)' : '(Transit)') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </details>
        </x-card>

        <!-- ===== GRID PILIHAN KELAS ===== -->
        <div class="flex-1 grid sm:grid-cols-2 gap-6">
            @foreach($flight->classes as $class)
                <x-card class="overflow-hidden flex flex-col p-0 bg-surface">
                    <div class="h-40 overflow-hidden relative">
                        @if($class->class_type === 'business')
                            <img src="{{ asset('images/Lufthansa-Boeing-787-Dreamliner-Business-Class-Frankfurt-Newark-EWR-Zach-Griff-61.webp') }}"
                                 alt="Business Cabin"
                                 class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/asiana_economy03-1024x576.jpg') }}"
                                 alt="Economy Cabin"
                                 class="w-full h-full object-cover">
                        @endif
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <p class="font-bold text-lg text-primary">{{ ucfirst($class->class_type) }} Class</p>
                                @if($class->class_type === 'business')
                                    <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-0.5 rounded font-bold uppercase">Gold Standard</span>
                                @endif
                            </div>
                            <p class="font-extrabold text-2xl text-on-surface mb-4">
                                Rp {{ number_format($class->price, 0, ',', '.') }}
                            </p>

                            <div class="space-y-2 mb-6">
                                @foreach($class->facilties as $facility)
                                    <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                        <img src="{{ $facility->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($facility->image) ? asset('storage/' . $facility->image) : asset('images/logo.svg') }}"
                                            alt="{{ $facility->name }}"
                                            class="w-4 h-4 object-contain">
                                        {{ $facility->name }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <x-button variant="primary" size="lg" href="{{ route('booking.choose-seat', ['flight' => $flight->flight_number, 'flightClass' => $class->id, 'passengers' => $passengers]) }}">
                            Pilih Kelas
                        </x-button>
                    </div>
                </x-card>
            @endforeach
        </div>

    </div>
</div>
@endsection
