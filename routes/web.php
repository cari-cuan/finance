<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Models\Package;
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
        'packages' => Package::where('is_active', true)->get(),
    ]);
})->middleware('auth')->name('paywall');

Route::middleware(['auth', 'licensed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/catat', [TransactionController::class, 'index'])->name('catat');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    Route::get('/chat', [AiChatController::class, 'index'])->name('chat');

    Route::get('/rekap', [ReportController::class, 'index'])->name('rekap');
    Route::get('/rekap/export', [ReportController::class, 'export'])->name('rekap.export');
    Route::post('/rekap/email', [ReportController::class, 'email'])->name('rekap.email');

    Route::post('/vouchers/validate', [CheckoutController::class, 'validateVoucher'])->name('vouchers.validate');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
});

// AI Chat API endpoint (outside licensed middleware to avoid Inertia JSON conflict)
Route::middleware('auth')->post('/chat/process', [AiChatController::class, 'process'])->name('chat.process');
Route::middleware('auth')->get('/chat/history', [AiChatController::class, 'history'])->name('chat.history');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
});

Route::get('/reports', function () {
    return redirect()->route('rekap');
});
