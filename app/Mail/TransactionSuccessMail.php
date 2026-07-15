<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Class TransactionSuccessMail
 * 
 * Mailable untuk mengirim email notifikasi sukses transaksi beserta boarding pass.
 * 
 * PERUBAHAN DARI KODE ASLI:
 * Menambahkan implementasi kontrak `ShouldQueue` agar pengiriman email ini dimasukkan ke antrean background job (Queue).
 * Mengingat lampiran PDF dibuat menggunakan DomPDF yang lambat dan CPU-intensive, antrean mencegah 
 * proses pengiriman memblokir jalannya respons HTTP pengguna/webhook pembayaran.
 */
class TransactionSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        // Eager load semua relasi yang dibutuhkan boarding pass
        // supaya tidak N+1 query saat render PDF
        $this->transaction = $transaction->loadMissing([
            'flight.airline',
            'flight.segments.airport',
            'class',
            'passengers.seat',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Transaction Successful - Boarding Pass');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.transaction-success',
            with: ['transaction' => $this->transaction],
        );
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.boarding-pass', [
            'transaction' => $this->transaction,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'boarding-pass-' . $this->transaction->code . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
