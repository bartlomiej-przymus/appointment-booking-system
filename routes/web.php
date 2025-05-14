<?php

use App\Http\Controllers\PaymentController;
use App\Livewire\Payment;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get(
        '/payment/{appointmentId}',
        Payment::class
    )->name('payment');
    Route::get(
        '/payment/success',
        [PaymentController::class, 'success']
    )->name('payment.success');
});


require __DIR__.'/auth.php';
