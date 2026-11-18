<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FieldController as AdminFieldController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\HistoryController;
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

    Route::get('/riwayat', [HistoryController::class, 'index'])->name('riwayat.index');
    Route::post('/riwayat/{booking:code}/batal', [HistoryController::class, 'batal'])->name('riwayat.batal');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/booking', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/booking/{booking:code}/bukti', [AdminBookingController::class, 'bukti'])->name('bookings.bukti');
        Route::post('/booking/{booking:code}/setujui', [AdminBookingController::class, 'setujui'])->name('bookings.setujui');
        Route::post('/booking/{booking:code}/tolak', [AdminBookingController::class, 'tolak'])->name('bookings.tolak');

        Route::get('/lapangan', [AdminFieldController::class, 'index'])->name('fields.index');
        Route::get('/lapangan/tambah', [AdminFieldController::class, 'create'])->name('fields.create');
        Route::post('/lapangan', [AdminFieldController::class, 'store'])->name('fields.store');
        Route::get('/lapangan/{field}/ubah', [AdminFieldController::class, 'edit'])->name('fields.edit');
        Route::put('/lapangan/{field}', [AdminFieldController::class, 'update'])->name('fields.update');
        Route::post('/lapangan/{field}/status', [AdminFieldController::class, 'status'])->name('fields.status');

        Route::get('/pengguna', [AdminUserController::class, 'index'])->name('users.index');

        Route::get('/laporan', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/csv', [AdminReportController::class, 'csv'])->name('reports.csv');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
