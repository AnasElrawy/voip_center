<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


// Route::get('/customer/verify-email', [CustomerAuthController::class, 'verifyEmail'])->name('customer.verify.email');
// Route::get('/customer/resend-verification', [CustomerAuthController::class, 'resendVerification'])->name('customer.resend.verification');

// routes/web.php
Route::prefix('customer')->group(function () {

    Route::get('register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register.form');
    Route::post('register', [CustomerAuthController::class, 'register'])->name('customer.register');
    
    Route::get('verify-email', [CustomerAuthController::class, 'verifyEmail'])->name('verify.email');
    
    Route::get('verify-notice', [CustomerAuthController::class, 'showVerifyNotice']) ->name('customer.verify.notice');
    
    Route::get('verify-resend', [CustomerAuthController::class, 'showResendForm'])->name('customer.verify.resend.form');
    Route::post('verify-resend', [CustomerAuthController::class, 'resendVerificationEmail'])->name('customer.verify.resend');

    Route::get('login', [CustomerAuthController::class, 'showLoginForm']) ->name('customer.login.form');
    Route::post('login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');
    
    Route::post('logout', [CustomerAuthController::class, 'logout']) ->name('customer.logout');
    
});


Route::get('/get-ip-info', function (Request $request) {
    $ip = $request->ip(); 
    $key = config('my_app_settings.ipstack.access_key');
    
    $url = "http://api.ipstack.com/{$ip}?access_key={$key}";
    $response = Http::get($url);

    return $response->json();
});

