<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/lapangan', [FieldController::class, 'index'])->name('lapangan.index');
Route::get('/lapangan/{field}', [FieldController::class, 'show'])->name('lapangan.show');

Route::middleware('auth')->group(function () {
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

    Route::get('/pembayaran/{booking:code}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{booking:code}', [PaymentController::class, 'store'])->name('pembayaran.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';