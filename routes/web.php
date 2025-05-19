<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerController;
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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('auth.login');
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
    
    ///////////set in medell ware auth  })->middleware(['auth', 'verified']);
    // Route::get('logout', [CustomerAuthController::class, 'logout']) ->name('customer.logout');
    ///////////////  
    
    Route::get('forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('customer.forgotPassword.form');
    Route::post('forgot-password', [CustomerAuthController::class, 'sendForgotPasswordEmail'])->name('customer.forgotPassword.send');
   
    Route::get('reset-password', [CustomerAuthController::class, 'showResetPasswordForm'])->name('customer.resetPassword.form');
    Route::post('reset-password', [CustomerAuthController::class, 'resetPassword'])->name('customer.resetPassword');

    // Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('customer.forgotPassword.email');

});


Route::get('/get-ip-info', function (Request $request) {
    // $ip = $request->ip(); 
    $ip = $request->header('CF-Connecting-IP') ??
    $request->header('X-Forwarded-For') ??
    $request->ip();

    $key = config('my_app_settings.ipstack.access_key');
    
    $url = "http://api.ipstack.com/{$ip}?access_key={$key}";
    $response = Http::get($url);

    return $response->json();
});

// Route::get('/my-ip', function (\Illuminate\Http\Request $request) {
//     return response()->json([
//         'ip_from_request_ip' => $request->ip(),
//         'forwarded_for' => $request->header('X-Forwarded-For'),
//         'cf_ip' => $request->header('CF-Connecting-IP'),
//         'remote_addr' => $_SERVER['REMOTE_ADDR'],
//     ]);
// });


Route::middleware(['auth:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('customer.dashboard');

    Route::post('logout', [CustomerAuthController::class, 'logout']) ->name('logout');

});



