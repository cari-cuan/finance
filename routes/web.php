<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/paywall', function () {
    return Inertia::render('Paywall', [
        'packages' => App\Models\Package::where('is_active', true)->get()
    ]);
})->middleware('auth')->name('paywall');

Route::middleware(['auth', 'licensed'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/chat', [App\Http\Controllers\TransactionController::class, 'index'])->name('chat');
    Route::post('/transactions', [App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store');

    Route::get('/rekap', [App\Http\Controllers\ReportController::class, 'index'])->name('rekap');
    Route::get('/rekap/export', [App\Http\Controllers\ReportController::class, 'export'])->name('rekap.export');
    Route::post('/rekap/email', [App\Http\Controllers\ReportController::class, 'email'])->name('rekap.email');

    Route::post('/vouchers/validate', [App\Http\Controllers\CheckoutController::class, 'validateVoucher'])->name('vouchers.validate');
    Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');

    Route::post('/chat/process', [ChatController::class, 'process'])->name('chat.process');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle', [App\Http\Controllers\AdminController::class, 'toggleUserStatus'])->name('users.toggle');
});

Route::get('/reports', function () {
    return redirect()->route('rekap');
});
