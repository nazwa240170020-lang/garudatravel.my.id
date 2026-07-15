<div>
    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">Promo Code</label>

        <div class="modal-promo-row">
            <input
                type="text"
                class="form-input no-icon"
                placeholder="e.g. GARUDA20"
                wire:model.defer="promoCodeInput"
                wire:keydown.enter="applyPromo"
                style="text-transform:uppercase"
            >

            <button
                type="button"
                class="apply-btn"
                wire:click="applyPromo"
                wire:loading.attr="disabled"
                wire:target="applyPromo"
                style="background:#1a1a2e;color:#fff;border:none;border-radius:12px;padding:0 18px;font-size:0.85rem;font-weight:600;cursor:pointer;white-space:nowrap;font-family:inherit"
            >
                <span wire:loading.remove wire:target="applyPromo">
                    Apply
                </span>

                <span wire:loading wire:target="applyPromo">
                    Checking...
                </span>
            </button>
        </div>

        @if($promoMessage)
            <p class="modal-promo-msg {{ $promoStatus }}" style="margin-top:8px">
                {{ $promoMessage }}
            </p>
        @endif
    </div>
</div>