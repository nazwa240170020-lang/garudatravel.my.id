@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-6 py-10">
    <x-button variant="secondary" href="{{ route('booking.detail', $transaction->id) }}" class="mb-6">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Detail
    </x-button>

    <h1 class="font-display text-headline-lg text-primary mb-6">Pembayaran</h1>

    @if(session('error'))
        <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 mb-5 text-body-sm text-error">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="flex items-start gap-3 border rounded-md p-4 mb-5 text-body-sm bg-blue-100 text-blue-700 border-blue-400">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('info') }}
        </div>
    @endif

    <div id="payment-status-alert" class="hidden mb-5"></div>

    <x-card padding="6" class="space-y-5">
        <div>
            <p class="text-label-md text-tertiary">Kode Booking</p>
            <p class="font-display text-headline-sm text-primary">{{ $transaction->code }}</p>
        </div>

        <hr class="border-border">

        <div class="flex justify-between items-center">
            <p class="text-label-md text-tertiary">Total Bayar</p>
            <p class="font-display text-headline-md text-secondary">
                Rp {{ number_format($transaction->grandtotal, 0, ',', '.') }}
            </p>
        </div>

        <hr class="border-border">

        @if($snapToken)
            <button onclick="handlePay()"
                class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-surface rounded-full px-6 py-4 font-semibold text-body-md transition shadow-md">
                Bayar Sekarang
            </button>
            <p class="text-center text-label-md text-tertiary">Status pembayaran akan diperiksa secara otomatis.</p>
        @else
            <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 text-body-sm text-error">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Sistem pembayaran tidak tersedia. Silakan coba lagi nanti.
            </div>
        @endif

        <div class="text-center">
            <x-button variant="tertiary" href="{{ route('booking.detail', $transaction->id) }}">
                Kembali ke Detail
            </x-button>
        </div>
    </x-card>
</div>

@push('scripts')
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
    var finishUrl = '{{ route("booking.finish", $transaction->id) }}';
    var detailUrl = '{{ route("booking.detail", $transaction->id) }}';
    var csrfToken = '{{ csrf_token() }}';

    function handlePay() {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                showAlert('Pembayaran berhasil! Memproses...', 'success');
                confirmPayment(0, result);
            },
            onPending: function(result) {
                showAlert('Menunggu konfirmasi pembayaran...', 'info');
                confirmPayment(0, result);
            },
            onError: function(result) {
                showAlert('Pembayaran gagal. Silakan coba lagi.', 'error');
                console.error('Snap error:', result);
            },
            onClose: function() {
                showAlert('Memeriksa status pembayaran...', 'info');
                confirmPayment();
            }
        });
    }

    /**
     * Konfirmasi pembayaran via AJAX ke finish endpoint.
     * Server cek ke Midtrans API → update DB → return JSON.
     * Jika API error (404), server bisa menggunakan body JSON sebagai fallback.
     */
    function confirmPayment(retryCount, snapResult) {
        retryCount = retryCount || 0;
        var maxRetries = 10;
        
        var fetchOptions = {
            method: snapResult ? 'POST' : 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        };

        if (snapResult) {
            fetchOptions.headers['Content-Type'] = 'application/json';
            fetchOptions.body = JSON.stringify(snapResult);
        }

        fetch(finishUrl, fetchOptions)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'paid') {
                showAlert('Pembayaran berhasil! Mengalihkan...', 'success');
                setTimeout(function() {
                    window.location.href = data.redirect_url;
                }, 800);
            } else if (data.status === 'failed') {
                showAlert('Pembayaran gagal atau kedaluwarsa.', 'error');
                setTimeout(function() {
                    window.location.href = data.redirect_url || detailUrl;
                }, 1500);
            } else if (data.status === 'pending' && retryCount < maxRetries) {
                showAlert('Menunggu konfirmasi pembayaran... (' + (retryCount + 1) + '/' + maxRetries + ')', 'info');
                setTimeout(function() {
                    confirmPayment(retryCount + 1);
                }, 3000);
            } else {
                showAlert('Pembayaran masih diproses. Halaman akan dimuat ulang.', 'info');
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(function(err) {
            console.error('Confirm payment error:', err);
            if (retryCount < 3) {
                setTimeout(function() {
                    confirmPayment(retryCount + 1);
                }, 2000);
            } else {
                showAlert('Gagal memeriksa status. Silakan muat ulang halaman.', 'error');
            }
        });
    }

    function showAlert(message, type) {
        var el = document.getElementById('payment-status-alert');
        el.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-blue-100', 'text-blue-700', 'bg-red-100', 'text-red-700');
        var bg = type === 'success' ? 'bg-green-100 text-green-700' :
                 type === 'error' ? 'bg-red-100 text-red-700' :
                 'bg-blue-100 text-blue-700';
        el.className = bg + ' px-4 py-3 rounded-md text-body-sm font-medium';
        el.textContent = message;
    }
</script>
@endpush
@endsection
