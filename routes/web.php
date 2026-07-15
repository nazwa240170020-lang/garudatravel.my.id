<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'landing'])->name('landing');
Route::get('/api/airports', [DashboardController::class, 'getAirports'])->name('api.airports');





Route::middleware(['auth', 'verified', 'redirect.admin'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/flights', [DashboardController::class, 'flights'])->name('flights');

Route::post('/midtrans/webhook', [BookingController::class, 'webhook'])->name('midtrans.webhook');

Route::middleware(['auth', 'verified', 'redirect.admin'])->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/flight/{flight:flight_number}/choose-tier',
        [DashboardController::class, 'chooseTier'])->name('flight.choose-tier');

    Route::get('/flight/{flight:flight_number}/booking/{flightClass}/choose-seat',
        [BookingController::class, 'chooseSeat'])->name('booking.choose-seat');

    Route::get('/promo/check', [BookingController::class, 'checkPromo'])->name('promo.check');

    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/check',  [BookingController::class, 'check'])->name('booking.check');

    Route::get('/booking/{transaction}/payment',
        [BookingController::class, 'payment'])->name('booking.payment');

    Route::match(['get', 'post'], '/booking/{transaction}/finish',
        [BookingController::class, 'finish'])->name('booking.finish');

    Route::post('/booking/{transaction}/pay-bank',
        [BookingController::class, 'payBank'])->name('booking.payBank');

    Route::get('/booking/{transaction}',
        [BookingController::class, 'detail'])->name('booking.detail');

    Route::post('/booking/{transaction}/cancel',
        [BookingController::class, 'cancel'])->name('booking.cancel');

    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('booking.my-bookings');

    Route::get('/ajax/seats',          [BookingController::class, 'ajaxSeats'])->name('ajax.seats');
    Route::get('/ajax/payment-status/{transaction}',
        [BookingController::class, 'ajaxPaymentStatus'])->name('ajax.payment-status');
});

require __DIR__.'/auth.php';
