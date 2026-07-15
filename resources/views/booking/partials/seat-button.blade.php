@if($seat)
    @php
        $isBooked = !$seat->is_available;
    @endphp

    <button
        type="button"
        class="seat-btn w-10 h-10 rounded-md flex items-center justify-center text-body-sm font-bold transition duration-200
            @if($isBooked)
                bg-gray-100 text-gray-300 cursor-not-allowed border border-gray-200
            @else
                bg-surface text-on-surface border border-border hover:bg-primary-tint hover:text-primary hover:border-primary
            @endif"
        data-seat-id="{{ $seat->id }}"
        data-seat-name="{{ $seat->name }}"
        data-status="{{ $isBooked ? 'booked' : 'available' }}"
        {{ $isBooked ? 'disabled' : '' }}
    >
        {{ $seat->name }}
    </button>
@else
    {{-- slot kosong, supaya grid tetap sejajar walau kursi gak ada di posisi ini --}}
    <div class="w-10 h-10"></div>
@endif
