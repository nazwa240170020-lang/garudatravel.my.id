<div>
    @php
        $groupSize = $classType === 'business' ? 2 : 3;
    @endphp
    <div class="flex flex-col gap-4 items-center" wire:poll.5s>
        @foreach($seatsByRow as $rowLabel => $rowSeats)
            <div class="flex gap-12">
                @php
                    $sortedSeats = $rowSeats->sortBy('column')->values();
                    $groups = $sortedSeats->chunk($groupSize);
                @endphp

                @foreach($groups as $group)
                    <div class="flex gap-3">
                        @foreach($group as $seat)
                            @php
                                $isSelected = in_array($seat->name, $selectedSeats);
                            @endphp
                            <button 
                                type="button"
                                wire:click="selectSeat('{{ $seat->name }}', {{ $seat->is_available ? 'true' : 'false' }})"
                                class="w-10 h-10 rounded-sm text-body-sm font-bold transition flex items-center justify-center border 
                                    @if(!$seat->is_available)
                                        bg-gray-300 text-gray-400 cursor-not-allowed border-gray-300
                                    @elseif($isSelected)
                                        bg-primary text-surface border-primary
                                    @else
                                        bg-surface text-on-surface border-border hover:border-primary/50
                                    @endif"
                                @if(!$seat->is_available) disabled @endif
                                title="{{ !$seat->is_available ? 'Dipesan' : ($isSelected ? 'Dipilih' : 'Tersedia') }}"
                            >
                                {{ $seat->row }}{{ $seat->column }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
