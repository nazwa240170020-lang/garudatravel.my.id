@extends('layouts.app')

@section('content')
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

    $subtotal   = $flightClass->price * $passengers;
    $tax        = round($subtotal * 0.11);
    $grandTotal = $subtotal + $tax;

    $selectedSeats = request('seats') ? explode(',', request('seats')) : [];
@endphp

<style>
    @keyframes spin{to{transform:rotate(360deg)}}
</style>

@livewireStyles

{{-- HERO --}}
<div class="px-6 py-16 lg:px-12 lg:py-20 relative overflow-hidden">
        <x-button variant="secondary" href="{{ route('booking.choose-seat', ['flight' => $flight->flight_number, 'flightClass' => $flightClass->id, 'passengers' => $passengers, 'seats' => request('seats')]) }}" class="mb-6 bg-surface/80 border-transparent text-primary hover:bg-surface">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Kembali ke Pilih Kursi
        </x-button>
        <h1 class="font-display text-headline-display text-surface leading-tight">Data Penumpang</h1>
        <p class="text-body-lg text-surface/80 mt-2">Lengkapi data di bawah untuk melanjutkan pemesanan</p>
    </div>
    <div class="absolute -right-20 top-1/2 -translate-y-1/2 opacity-10 pointer-events-none">
        <svg width="500" height="300" viewBox="0 0 700 420" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="330" cy="210" rx="270" ry="70" fill="white" opacity=".95"/>
            <path d="M590 185 Q660 210 590 235 L560 210Z" fill="white" opacity=".9"/>
            <path d="M60 190 Q20 210 60 230 L100 210Z" fill="white" opacity=".9"/>
            <path d="M200 220 L350 180 L480 210 L350 250Z" fill="white" opacity=".85"/>
            <ellipse cx="330" cy="210" rx="260" ry="18" fill="#4f46e5" opacity=".18"/>
            <g fill="#bfdbfe" opacity=".7">
                <rect x="300" y="202" width="14" height="9" rx="3"/><rect x="325" y="202" width="14" height="9" rx="3"/>
                <rect x="350" y="202" width="14" height="9" rx="3"/><rect x="375" y="202" width="14" height="9" rx="3"/>
                <rect x="400" y="202" width="14" height="9" rx="3"/><rect x="425" y="202" width="14" height="9" rx="3"/>
                <rect x="450" y="202" width="14" height="9" rx="3"/><rect x="475" y="202" width="14" height="9" rx="3"/>
                <rect x="500" y="202" width="14" height="9" rx="3"/><rect x="525" y="202" width="14" height="9" rx="3"/>
            </g>
            <path d="M100 200 L130 120 L165 200Z" fill="white" opacity=".9"/>
            <ellipse cx="280" cy="265" rx="40" ry="14" fill="#e0e7ff" opacity=".8"/>
            <ellipse cx="280" cy="265" rx="30" ry="9" fill="#c7d2fe" opacity=".8"/>
        </svg>
    </div>
</div>

{{-- FORM --}}
<form id="bookingForm" method="POST" action="{{ route('booking.store') }}">
@csrf
<input type="hidden" name="flight_id"            value="{{ $flight->id }}">
<input type="hidden" name="flight_class_id"       value="{{ $flightClass->id }}">
<input type="hidden" name="number_of_passengers"  value="{{ $passengers }}">
<input type="hidden" name="promo_code" id="final-promo-code" value="">
<input type="hidden" name="discount" id="final-discount" value="0">

<div class="max-w-6xl mx-auto px-6 -mt-16 pb-16 grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-6 items-start relative z-10">

  {{-- LEFT --}}
  <div>

    {{-- Flash error dari controller --}}
    @if(session('error'))
      <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 mb-5 text-body-sm text-error">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
      </div>
    @endif

    {{-- Laravel validation errors --}}
    @if($errors->any())
      <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 mb-5 text-body-sm text-error">
        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
          <strong>Mohon periksa kembali isian berikut:</strong>
          <ul class="mt-1 pl-4 list-disc">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    {{-- YOUR FLIGHT --}}
    <x-card class="mb-5">
      <div class="flex justify-between items-center cursor-pointer select-none" onclick="toggleCard('flight-card','toggle-flight')">
        <h3 class="font-display text-headline-sm text-primary">Penerbangan Anda</h3>
        <span class="toggle-btn open w-8 h-8 bg-secondary/10 rounded-full flex items-center justify-center shrink-0 transition" id="toggle-flight">
          <svg class="w-4 h-4 text-tertiary transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></svg>
        </span>
      </div>
      <div id="flight-card">
        <div class="grid grid-cols-2 gap-2 gap-x-6 mt-4 mb-5">
          <div>
            <div class="text-label-md text-tertiary">Keberangkatan</div>
            <div class="text-body-md font-semibold text-primary">{{ $firstSegment?->airport?->name }} ({{ $firstSegment?->airport?->iata_code }})</div>
          </div>
          <div>
            <div class="text-label-md text-tertiary">Kedatangan</div>
            <div class="text-body-md font-semibold text-primary">{{ $lastSegment?->airport?->name }} ({{ $lastSegment?->airport?->iata_code }})</div>
          </div>
          <div>
            <div class="text-label-md text-tertiary">Tanggal</div>
            <div class="text-body-md font-semibold text-primary">{{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('d F Y') : '-' }}</div>
          </div>
          <div>
            <div class="text-label-md text-tertiary">Jumlah</div>
            <div class="text-body-md font-semibold text-primary">{{ $passengers }} Orang</div>
          </div>
        </div>
        <div class="flex items-center gap-4 bg-neutral border border-border rounded-md p-4 mb-5">
          <div class="w-11 h-11 rounded-sm bg-surface border border-border flex items-center justify-center shrink-0 overflow-hidden">
            <img src="{{ $flight->airline->logo_url }}" alt="{{ $flight->airline->name }}" class="w-9 h-9 object-contain">
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-body-sm text-tertiary truncate">{{ $flight->airline?->name }} &middot; {{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('H:i') : '-' }} &ndash; {{ $lastSegment?->time ? \Carbon\Carbon::parse($lastSegment->time)->format('H:i') : '-' }}</div>
            <div class="flex items-center gap-2 font-display text-headline-sm text-primary mt-0.5">
              <span>{{ $firstSegment?->airport?->iata_code }}</span>
              <div class="flex-1 flex items-center gap-1">
                <hr class="flex-1 border-t border-dashed border-border">
                <svg class="w-3.5 h-3.5 text-tertiary" viewBox="0 0 24 24" fill="currentColor"><path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                <hr class="flex-1 border-t border-dashed border-border">
              </div>
              <span>{{ $lastSegment?->airport?->iata_code }}</span>
            </div>
            <div class="text-body-sm text-tertiary mt-0.5">
              @if($durationMinutes) {{ intdiv($durationMinutes, 60) }} Jam {{ $durationMinutes % 60 }} Menit &middot; @endif
              {{ $transitCount === 0 ? 'Langsung' : 'Transit '.$transitCount.'x' }}
            </div>
          </div>
          <div class="font-display text-headline-sm text-secondary shrink-0">Rp {{ number_format($flightClass->price, 0, ',', '.') }}</div>
        </div>

        @if(count($selectedSeats) > 0)
        <div>
          <div class="text-label-md text-tertiary mb-2">Kursi Dipilih</div>
          <div class="flex flex-wrap gap-2">
            @foreach($selectedSeats as $seatName)
              <span class="chip bg-secondary/10 text-secondary border-secondary/30">{{ $seatName }}</span>
            @endforeach
          </div>
        </div>
        @endif
      </div>
    </x-card>

    {{-- CUSTOMER INFORMATION --}}
    <x-card class="mb-5">
      <div class="flex justify-between items-center cursor-pointer select-none" onclick="toggleCard('customer-card','toggle-customer')">
        <h3 class="font-display text-headline-sm text-primary">Data Pemesan</h3>
        <span class="toggle-btn open w-8 h-8 bg-secondary/10 rounded-full flex items-center justify-center shrink-0 transition" id="toggle-customer">
          <svg class="w-4 h-4 text-tertiary transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></svg>
        </span>
      </div>
      <div id="customer-card">
        <div class="space-y-4 mt-4">
          <div class="space-y-1">
            <label class="text-label-md text-secondary">Nama Lengkap</label>
            <div class="relative">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <input type="text" name="name"
                     class="input-field pl-10 @error('name') border-error @enderror"
                     placeholder="Masukkan nama lengkap"
                     value="{{ old('name', Auth::user()->name) }}" required>
            </div>
            @error('name') <p class="text-label-sm text-error mt-1">{{ $message }}</p> @enderror
          </div>
          <div class="space-y-1">
            <label class="text-label-md text-secondary">Email</label>
            <div class="relative">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
              </div>
              <input type="email" name="email"
                     class="input-field pl-10 @error('email') border-error @enderror"
                     placeholder="Tulis email aktif"
                     value="{{ old('email', Auth::user()->email) }}" required>
            </div>
            @error('email') <p class="text-label-sm text-error mt-1">{{ $message }}</p> @enderror
          </div>
          <div class="space-y-1">
            <label class="text-label-md text-secondary">No. Telepon</label>
            <div class="relative">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11a19.79 19.79 0 01-3.07-8.67A2 2 0 012 .18h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
              </div>
              <input type="text" name="phone"
                     class="input-field pl-10 @error('phone') border-error @enderror"
                     placeholder="Tulis nomor aktif"
                     value="{{ old('phone') }}" required>
            </div>
            @error('phone') <p class="text-label-sm text-error mt-1">{{ $message }}</p> @enderror
          </div>
        </div>
      </div>
    </x-card>

    {{-- PASSENGER CARDS --}}
    @php $seatList = array_values($selectedSeats); @endphp

    @for($i = 1; $i <= $passengers; $i++)
      @php $seatLabel = $seatList[$i-1] ?? '-'; @endphp
      <x-card class="mb-5">
        <div class="flex justify-between items-center cursor-pointer select-none" onclick="toggleCard('pax-{{ $i }}-body','toggle-pax-{{ $i }}')">
          <div class="flex items-center gap-3">
            <span class="w-7 h-7 bg-primary text-surface rounded-full flex items-center justify-center text-label-md font-bold shrink-0">{{ $i }}</span>
            <h3 class="font-display text-headline-sm text-primary">Penumpang {{ $i }}</h3>
          </div>
          <div class="flex items-center gap-2">
            <span class="chip bg-primary-tint text-primary border-transparent">Kursi {{ $seatLabel }}</span>
            <span class="toggle-btn open w-8 h-8 bg-primary-tint rounded-full flex items-center justify-center shrink-0 transition" id="toggle-pax-{{ $i }}">
              <svg class="w-4 h-4 text-tertiary transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></svg>
            </span>
          </div>
        </div>
        <div id="pax-{{ $i }}-body">
          @php $matchedSeat = $availableSeats->first(fn($s) => $s->name === $seatLabel); @endphp
          <input type="hidden" name="passengers[{{ $i-1 }}][seat_id]" value="{{ $matchedSeat?->id ?? '' }}">

          <div class="space-y-4 mt-4">
            <div class="space-y-1">
              <label class="text-label-md text-secondary">Nama Lengkap</label>
              <div class="relative">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <input type="text"
                       name="passengers[{{ $i-1 }}][name]"
                       class="input-field pl-10 @error('passengers.'.($i-1).'.name') border-error @enderror"
                       placeholder="Nama lengkap penumpang"
                       value="{{ old('passengers.'.($i-1).'.name') }}"
                       required>
              </div>
              @error('passengers.'.($i-1).'.name')
                <p class="text-label-sm text-error mt-1">{{ $message }}</p>
              @enderror
            </div>

            <div class="space-y-1">
              <label class="text-label-md text-secondary">Tanggal Lahir</label>
              <div class="grid grid-cols-3 gap-2">
                <div class="relative">
                  <select name="pax_{{ $i }}_day" class="input-field pr-8 appearance-none" onchange="updateDob({{ $i }})">
                    <option value="">Hari</option>
                    @for($d=1;$d<=31;$d++)
                      <option value="{{ str_pad($d,2,'0',STR_PAD_LEFT) }}">{{ $d }}</option>
                    @endfor
                  </select>
                </div>
                <div class="relative">
                  <select name="pax_{{ $i }}_month" class="input-field pr-8 appearance-none" onchange="updateDob({{ $i }})">
                    <option value="">Bulan</option>
                    @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $mi => $ml)
                      <option value="{{ str_pad($mi+1,2,'0',STR_PAD_LEFT) }}">{{ $ml }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="relative">
                  <select name="pax_{{ $i }}_year" class="input-field pr-8 appearance-none" onchange="updateDob({{ $i }})">
                    <option value="">Tahun</option>
                    @for($y=date('Y');$y>=1940;$y--)
                      <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                  </select>
                </div>
              </div>
              <input type="hidden" name="passengers[{{ $i-1 }}][dob]" id="dob-{{ $i }}">
            </div>

            <div class="space-y-1">
              <label class="text-label-md text-secondary">Kewarganegaraan</label>
              <div class="relative">
                <select name="passengers[{{ $i-1 }}][nationality]"
                        class="input-field pr-8 appearance-none @error('passengers.'.($i-1).'.nationality') border-error @enderror"
                        required>
                  <option value="">Pilih negara</option>
                  @foreach(['Indonesia','Malaysia','Singapura','Jepang','Korea Selatan','China','Amerika Serikat','Inggris','Australia','Arab Saudi','Uni Emirat Arab','Lainnya'] as $country)
                    <option {{ old('passengers.'.($i-1).'.nationality') === $country ? 'selected' : '' }}>
                      {{ $country }}
                    </option>
                  @endforeach
                </select>
              </div>
              @error('passengers.'.($i-1).'.nationality')
                <p class="text-label-sm text-error mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>
      </x-card>
    @endfor

  </div>{{-- end left col --}}

  {{-- RIGHT SIDEBAR --}}
  <div class="lg:sticky lg:top-6">
    <x-card class="mb-5">
      <div class="flex justify-between items-center cursor-pointer select-none" onclick="toggleCard('summary-body','toggle-summary')">
        <h3 class="font-display text-headline-sm text-primary">Detail Transaksi</h3>
        <span class="toggle-btn open w-8 h-8 bg-secondary/10 rounded-full flex items-center justify-center shrink-0 transition" id="toggle-summary">
          <svg class="w-4 h-4 text-tertiary transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></svg>
        </span>
      </div>
      <div id="summary-body">
        <div class="space-y-2 mt-4">
          <div class="flex justify-between text-body-md"><span class="text-tertiary">Jumlah</span><span class="font-semibold text-primary">{{ $passengers }} Orang</span></div>
          <div class="flex justify-between text-body-md"><span class="text-tertiary">Kelas</span><span class="font-semibold text-primary">{{ ucfirst($flightClass->class_type) }}</span></div>
          <div class="flex justify-between text-body-md"><span class="text-tertiary">Kursi</span><span class="font-semibold text-primary">{{ implode(', ', $selectedSeats) ?: '-' }}</span></div>
          <hr class="border-border my-3">
          <div class="flex justify-between text-body-md"><span class="text-tertiary">Harga / org</span><span class="font-semibold text-primary">Rp {{ number_format($flightClass->price, 0, ',', '.') }}</span></div>
          <div class="flex justify-between text-body-md"><span class="text-tertiary">Pajak (11%)</span><span class="font-semibold text-primary">Rp {{ number_format($tax, 0, ',', '.') }}</span></div>
          <div class="flex justify-between text-body-md"><span class="text-tertiary">Sub Total</span><span class="font-semibold text-primary">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
          <hr class="border-border my-3">

          {{-- LIVEWIRE: Apply Promo --}}
          <livewire:apply-promo :subtotal="$subtotal" :tax="$tax" />

          <hr class="border-border my-3">
          <div class="flex justify-between text-body-md" id="discount-row" style="display:none">
            <span class="text-secondary">Diskon</span>
            <span class="font-semibold text-secondary" id="discount-val">-Rp 0</span>
          </div>
          <div class="flex justify-between text-body-md" id="promo-code-row" style="display:none">
            <span class="text-tertiary">Kode Promo</span>
            <span class="font-semibold text-primary" id="promo-code-display"></span>
          </div>
          <div class="flex justify-between items-center pt-3">
            <span class="text-label-md text-tertiary">Total</span>
            <span class="font-display text-headline-md text-primary" id="grand-total">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>
    </x-card>

    {{-- Tombol submit --}}
    <button type="submit" id="submit-btn"
      class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-surface rounded-full px-6 py-4 font-semibold text-body-md transition disabled:opacity-60 disabled:cursor-not-allowed shadow-md">
      <span class="spinner w-4 h-4 border-2 border-surface/40 border-t-surface rounded-full" style="display:none;animation:spin .7s linear infinite"></span>
      <span class="btn-text">Lanjut ke Pembayaran</span>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </button>

    <p class="text-center text-label-md text-tertiary mt-3">
      <svg class="w-3.5 h-3.5 inline align-middle mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      Data Anda aman dan terenkripsi
    </p>
  </div>

</div>
</form>

@livewireScripts
<script>
// Toggle accordion
function toggleCard(bodyId, toggleId) {
    const body = document.getElementById(bodyId);
    const btn  = document.getElementById(toggleId);
    if (!body || !btn) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : '';
    btn.classList.toggle('open', !open);
    const svg = btn.querySelector('svg');
    if (svg) {
        svg.style.transform = open ? '' : 'rotate(180deg)';
    }
}

// Update DOB hidden input
function updateDob(idx) {
    const day   = document.querySelector(`[name="pax_${idx}_day"]`).value;
    const month = document.querySelector(`[name="pax_${idx}_month"]`).value;
    const year  = document.querySelector(`[name="pax_${idx}_year"]`).value;
    if (day && month && year) {
        document.getElementById(`dob-${idx}`).value = `${year}-${month}-${day}`;
    }
}

// Submit button loading state
document.getElementById('bookingForm').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    const spinner = btn.querySelector('.spinner');
    const btnText = btn.querySelector('.btn-text');
    if (spinner) spinner.style.display = 'block';
    if (btnText) btnText.style.opacity = '0.7';
});

// Livewire promo event
document.addEventListener('livewire:init', () => {
    Livewire.on('promo-applied', (event) => {
        const data = Array.isArray(event) ? event[0] : event;

        document.getElementById('final-promo-code').value = data.code;
        document.getElementById('final-discount').value = data.discount;

        document.getElementById('grand-total').textContent =
            'Rp ' + Number(data.grandTotal).toLocaleString('id-ID');

        if (data.discount > 0) {
            document.getElementById('discount-row').style.display = 'flex';
            document.getElementById('discount-val').textContent =
                '-Rp ' + Number(data.discount).toLocaleString('id-ID');
            document.getElementById('promo-code-row').style.display = 'flex';
            document.getElementById('promo-code-display').textContent = data.code;
        } else {
            document.getElementById('discount-row').style.display = 'none';
            document.getElementById('promo-code-row').style.display = 'none';
        }
    });
});

// Auto-scroll ke error pertama
document.addEventListener('DOMContentLoaded', () => {
    const firstError = document.querySelector('[class*="bg-error"], .border-error');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Accordion open states: flight-card, customer-card, pax bodies, summary-body initially visible
['flight-card', 'customer-card', 'summary-body'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = '';
});
</script>
@endsection
