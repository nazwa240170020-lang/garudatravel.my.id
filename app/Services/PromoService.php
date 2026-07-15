<?php

namespace App\Services;

use App\Exceptions\PromoValidationException;
use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PromoService
{
    /**
     * Returns a usable promo, or null for callers that only need a boolean result.
     */
    public function validate(string $code, ?User $user = null): ?PromoCode
    {
        try {
            return $this->validateOrFail($code, $user);
        } catch (PromoValidationException) {
            return null;
        }
    }

    /**
     * Validates a promo with the user-specific one-use restriction.
     */
    public function validateOrFail(string $code, ?User $user = null): PromoCode
    {
        $promo = PromoCode::where('code', strtoupper(trim($code)))->first();

        if (! $promo) {
            throw new PromoValidationException('Kode promo tidak ditemukan.');
        }

        $this->assertUsable($promo, $user?->id);

        return $promo;
    }

    /**
     * Checks a promo model. This is also used after lockForUpdate at payment time.
     */
    public function assertUsable(PromoCode $promo, ?int $userId = null): void
    {
        if (! $promo->is_active) {
            throw new PromoValidationException('Kode promo sudah tidak aktif.');
        }

        if ($promo->valid_until !== null && $promo->valid_until->isPast()) {
            throw new PromoValidationException('Kode promo telah kedaluwarsa.');
        }

        if ($promo->usage_limit !== null && $promo->used_count >= $promo->usage_limit) {
            throw new PromoValidationException('Kode promo telah mencapai batas maksimum penggunaan.');
        }

        if ($userId && PromoCodeUsage::where('promo_code_id', $promo->id)->where('user_id', $userId)->exists()) {
            throw new PromoValidationException('Anda sudah pernah menggunakan kode promo ini.');
        }
    }

    public function calculateDiscount(PromoCode $promo, int $subtotal, int $tax): int
    {
        $total = $subtotal + $tax;

        $discount = $promo->discount_type === 'percentage'
            ? (int) round($total * min($promo->discount, 100) / 100)
            : min($promo->discount, $total);

        return max(0, $discount);
    }

    /**
     * Validates, records usage, increments the counter, and marks a payment paid
     * in one database transaction. The promo row is locked to protect its final slot.
     */
    public function completePayment(Transaction $transaction, ?string $paymentChannel = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $paymentChannel) {
            $lockedTransaction = Transaction::lockForUpdate()->findOrFail($transaction->id);

            if ($lockedTransaction->payment_status === 'paid') {
                return $lockedTransaction;
            }

            if ($lockedTransaction->promo_code_id) {
                $promo = PromoCode::lockForUpdate()->findOrFail($lockedTransaction->promo_code_id);
                $this->assertUsable($promo, $lockedTransaction->user_id);

                PromoCodeUsage::create([
                    'promo_code_id' => $promo->id,
                    'user_id' => $lockedTransaction->user_id,
                    'transaction_id' => $lockedTransaction->id,
                ]);

                $promo->increment('used_count');
            }

            $lockedTransaction->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'midtrans',
                'payment_channel' => $paymentChannel ?? $lockedTransaction->payment_channel,
            ]);

            return $lockedTransaction->fresh();
        }, 3);
    }

    public function apply(string $code, int $subtotal, int $tax, ?User $user = null): array
    {
        try {
            $promo = $this->validateOrFail($code, $user);
        } catch (PromoValidationException $exception) {
            return ['valid' => false, 'discount' => 0, 'message' => $exception->getMessage()];
        }

        $discount = $this->calculateDiscount($promo, $subtotal, $tax);
        $label = $promo->discount_type === 'percentage'
            ? $promo->discount . '%'
            : 'Rp ' . number_format($promo->discount, 0, ',', '.');

        return compact('promo', 'discount', 'label') + [
            'valid' => true,
            'discount_type' => $promo->discount_type,
            'code' => $promo->code,
        ];
    }
}
