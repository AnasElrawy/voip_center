<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\EmailVerification;
use App\Models\LoginLog;
// use App\Models\EmailVerification;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// use App\Mail\VerifyCustomerEmail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StoreCustomerRequest;





class CustomerAuthController extends Controller
{
    //

    public function showRegisterForm(Request $request)
    {

        // dd($request->ip());
        // dd($_SERVER['REMOTE_ADDR']);
        // dd(file_get_contents('https://ipapi.co/8.8.8.8/json/'));
        // echo file_get_contents('https://ipapi.co/8.8.8.8/json/');

        return view('auth.register');
    }


    public function register(StoreCustomerRequest  $request)
    {
        
        try {
            DB::beginTransaction();
    
            // 1. Store customer locally
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_full,
                'timezone' => $request->timezone,
                'is_active' => false,
            ]);

            $countryData = json_decode($request->input('CountryData'), true);
            $dialCode = $countryData['dialCode'] ?? null;

            // 2. Store customer in API
            $createCustomerApiResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'createcustomer',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $request->username,
                'customerpassword' => $request->customerpassword,
                // 'geocallcli' => $request->phone_full,
                // 'tariffrate' => $request->phone,
                'country' => $dialCode,
                'timezone' => $request->timezone,
            ],
            $messages = [

            // Email
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',

            // Username
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username can only contain letters, numbers, dashes, underscores, dots, and @ symbol.',

            // Phone (phone_full)
            'phone_full.required' => 'Phone number is required.',
            'phone_full.regex' => 'Please enter a valid international phone number (e.g., +441234567890).',
            'phone_full.unique' => 'This phone number is already in use.',

            // Password
            'customerpassword.required' => 'Password is required.',
            'customerpassword.string' => 'Password must be a valid string.',
            'customerpassword.min' => 'Password must be at least 4 characters.',
            'customerpassword.max' => 'Password cannot exceed 39 characters.',
            'customerpassword.regex' => 'Password can only contain letters, numbers, dashes, underscores, dots, and @ symbol.',

            // Optional fields
            'country_code.size' => 'Country code must be exactly 2 characters.',
            'timezone.max' => 'Timezone must not exceed 50 characters.',
            'ip_address.ip' => 'Please enter a valid IP address.',

        ]
        );

            $xml = simplexml_load_string($createCustomerApiResponse->body());

            // dd($createCustomerApiResponse, $xml ,'$xml->Result :'.(string) $xml->Result ,'(!$xml || (string) $xml->Result !== Success) :'.(!$xml || (string) $xml->Result !== 'Success') ,'$request->phone :'.$request->phone,'urlencode($request->phone :'. urlencode($request->phone));

            // if (!$xml || (string) $xml->Result !== 'Success') {
            if (!$xml || !isset($xml->CustomerLoginName)) {

                $customer->delete();
                DB::rollBack();
            
                return back()->with(
                    'error' , 'This username might already be taken on our provider. Please choose another one.',
                );
            }
            
    
            // 3. Temporarily block the customer 
            $changeuserinfoApiResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'changeuserinfo',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $request->username,
                'customerblocked' => 'true',
            ]);
    
    
            // 4. Generate email verification token
            do {
                $token = Str::random(64); 
            
                $existingToken = EmailVerification::where('token', $token)->first();

            } while ($existingToken); 
                
            EmailVerification::create([
                'email' => $request->email,
                'token' => $token,
                'expires_at' => Carbon::now()->addMinutes(30),
            ]);
    
            // 5. Send verification email
            Mail::to($request->email)->send(new \App\Mail\VerifyCustomerEmail($token, $request->email));
    
            DB::commit();
    
            return redirect()->route('customer.verify.notice');
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            return back()->with(
                'error', 'An error occurred during registration: ' . $e->getMessage(),
            );
        }
    }
    

    public function showVerifyNotice()
    {
        return view('auth.verify-notice');
    }


    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        $customer = Customer::where('email', $email)->first();

        ////1. check 

        // If the customer doesn't exist, redirect to registration with error
        if (!$customer) {
            return redirect()->route('customer.register.form')->withErrors(['msg' =>  'We couldn’t find your email address. Please register first.']);
        }

        // If the email is already verified and the account is active, redirect to login
        if ($customer->is_active && $customer->email_verified_at) {
            return redirect()->route('customer.login.form')->with('info', 'Your email is already verified. Please log in.');
        }

        $emailVerification = EmailVerification::where('token', $token)->first();

        // If the token is invalid or doesn't match the email, show error
        if (!$emailVerification || $emailVerification->email !== $email) {
            return redirect()->route('customer.login.form')->withErrors(['msg' => 'Invalid or expired verification link.']);
        }

        // If the verification link has expired, redirect to resend page with message
        if (Carbon::now()->gt($emailVerification->expires_at)) {

            // do {
            //     $token = Str::random(64); 
            
            //     $existingToken = EmailVerification::where('token', $token)->first();
                
            // } while ($existingToken); 
                
            // EmailVerification::create([
            //     'email' => $email,
            //     'token' => $token,
            //     'expires_at' => Carbon::now()->addMinutes(30),
            // ]);
    
            // // 5. Send verification email

            // Mail::to($email)->send(new \App\Mail\VerifyCustomerEmail($token, $email));     
            
            // return redirect()->route('login')->withErrors(['msg' => 'The verification link has expired.']);

            return redirect()->route('customer.verify.resend')->with('error', 'The verification link has expired. Please enter your email to receive a new one.');

        }

        //// 2.Unblock the customer in the external VoIP system
        try {

            $changeuserinfoApiResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'changeuserinfo',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $customer->username,  
                'customerblocked' => 'false', 
            ]);

            if ($changeuserinfoApiResponse->failed()) {
                return redirect()->route('customer.login.form')->withErrors(['msg' => 'We couldn’t connect to the verification service. Please try again later.']);
            }
            
            $responseXml = simplexml_load_string($changeuserinfoApiResponse->body());
            // Check if the response contains Result and Reason
            if ($responseXml->Result == 'Failed') {
                $reason = (string)$responseXml->Reason; // Get the reason for failure
                
                // Custom messages based on the reason
                if ($reason == 'Unknown customer') {
                    return redirect()->route('customer.login.form')->withErrors(['msg' => 'We couldn’t find your account. Please check the information you entered.']);
                }
                
                // If the request failed partially
                return redirect()->route('customer.login.form')->withErrors(['msg' => 'We couldn’t connect to the verification service. Please try again later.']);
            }


        } catch (\Exception $e) {
            return redirect()->route('customer.login.form')->withErrors(['msg' =>'We couldn’t connect to the verification service. Please try again later.']);
        }

        //// 3. Update the database: verify the customer's email and activate the account
        DB::beginTransaction();
        try {
            $customer = Customer::where('email', $email)->first();

            if ($customer) {
                $customer->email_verified_at = Carbon::now(); 
                $customer->is_active = true; 
                $customer->save();
            }

            EmailVerification::where('email', $email)->delete();

            DB::commit();  

            return redirect()->route('customer.login.form')->with('success', 'Your email has been successfully verified. You can now log in.');

        } catch (\Exception $e) {
            DB::rollBack();  
            return redirect()->route('customer.login.form')->withErrors(['msg' => 'Something went wrong. Please try again later.']);
        }
    }

    public function showResendForm()
    {
        return view('auth.verify-resend');
    }

    public function resendVerificationEmail(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:customers,email',
        ], [
            'email.exists' => 'We couldn’t find an account with that email address.',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if ($customer && $customer->email_verified_at) {
            return redirect()->route('customer.login')->with('info', 'Your email is already verified. You can login now.');
        }

        do {
            $token = Str::random(64); 
        
            $existingToken = EmailVerification::where('token', $token)->first();
            
        } while ($existingToken); 
        EmailVerification::create([
            'email' => $request->email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        Mail::to($request->email)->send(new \App\Mail\VerifyCustomerEmail($token, $request->email));

        // return redirect()->route('customer.login')->with('message', 'A new verification link has been sent to your email.');
        

        return back()->with('success', 'Verification email has been resent successfully.');
    }

    
    public function showLoginForm()
    {
        return view('auth.login');
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        // 1. ابحث عن المستخدم محلياً
        $customer = Customer::where('username', $request->username)->first();
    
        if (! $customer) {
            // return back()->withErrors(['msg' => 'No account found with that username.']);
            return redirect()->route('customer.login.form')->with('error' , 'Incorrect username or password.');
            // return redirect()->route('customer.login.form')->with('error' , 'No account found with that username.');
        }
    
        // 2. تحقق من تفعيل الحساب
        if (! $customer->is_active || !$customer->email_verified_at) {
            return back()->withErrors(['msg' => 'Your account is not activated yet. Please check your email.']);
        }
    
        // 3. تحقق من صحة البيانات عبر API
        $apiResponse = Http::get(config('my_app_settings.voip.api_url'), [
            'command' => 'validateuser',
            'username' => config('my_app_settings.voip.username'),
            'password' => config('my_app_settings.voip.password'),
            'customer' => $request->username,
            'customerpassword' => $request->password,
        ]);

        
        if ($apiResponse->failed()) {
            return redirect()->route('customer.login.form')->with('error' ,'Something is wrong. Please try again later.');
        }
        
        $ip = $request->header('CF-Connecting-IP') ??
        $request->header('X-Forwarded-For') ??
        $request->ip();

        $xml = simplexml_load_string($apiResponse);

// dd($xml);
        if ($xml && $xml->Result == 'Success') {

            // Store password in session
            $customerPassword = $request->input('password');  
            session(['customer_password' => Crypt::encryptString($customerPassword)]);

            // Get Blocked status
            $userName = $customer->username;
            $getInfoResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'getuserinfo',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $userName,
                'customerpassword' => $customerPassword,
            ]);

            $status = 'unknown'; // fallback
            $isBlocked = false;


            if ($getInfoResponse->ok()) {
                $userInfoXML = simplexml_load_string($getInfoResponse->body());
                $isBlocked = strtolower((string) $userInfoXML->Blocked) === 'true';
                $status = $isBlocked ? 'blocked' : 'active';


                // dd($status);
            }

//             dd([
//     'BlockedFromXML' => (string) $userInfoXML->Blocked,
//     'EvaluatedIsBlocked' => $isBlocked,
//     'FinalStatus' => $status,
// ]);


            // سجل اللوج بغض النظر
            LoginLog::create([
                'user_id' => $customer->id,
                'username' => $customer->username,
                'ip_address' => $ip,
                'login_time' => now(),
                'status' => $status,
            ]);

            // لو معمول له بلوك، امنعه من الدخول
            if ($isBlocked) {
                return redirect()->route('customer.login.form')->with('error', 'Your account is blocked. Please contact support.');
            }

            // سجل الدخول
            Auth::guard('customer')->login($customer);

            return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
        }

            return back()->with('error','Incorrect username or password.');



        // if ($xml && $xml->Result == 'Success') {

        //     // store the password in session 
        //     $customerPassword = $request->input('password');  
        //     session(['customer_password' => Crypt::encryptString($customerPassword)]);


        //     Auth::guard('customer')->login($customer);

        //     LoginLog::create([
        //         'user_id' => Auth::guard('customer')->id(),
        //         'username' => Auth::guard('customer')->user()->username,
        //         'ip_address' => $ip,
        //         'login_time' => now(),
        //     ]);

        //     return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
        // } else {
        //     // return redirect()->route('customer.forgotPassword.form')
        //     //                 ->withErrors(['email' => 'There was an error resetting your password. Please try again.']);
        //     return back()->with('error','Incorrect username or password.');
        // }


        // dd($apiResponse->body());
    
        // if (strtolower($apiResponse->body()) !== 'true') {
        //     return back()->withErrors(['msg' => 'Incorrect username or password.']);
        // }
    
        // DB::table('sessions')->delete();  // يحذف جميع الجلسات للمستخدمين

        // Auth::guard('customer')->logout();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        // 4. كل شيء تمام، سجل الدخول
        // Auth::guard('customer')->login($customer);

        // Auth::guard('customer')->login($customer);
        
        // Auth::guard('customer')->check();      // هل العميل مسجل دخول؟
        // Auth::guard('customer')->user();       // رجع بيانات العميل
        // Auth::guard('customer')->logout();     // تسجيل خروج العميل

        // dd(Auth::guard('customer')->check());
        // return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
    }

    
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login.form');
    }

    
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }


    public function sendForgotPasswordEmail(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:customers,email',
        ], [
            'email.exists' => 'We couldn’t find an account with that email address.',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        
        if ($customer->email_verified_at) {

            DB::table('password_resets')->where('email', $request->email)->delete();

            do {
                $token = Str::random(64); 
            
                $existingToken = db::table('password_resets')->where('token', $token)->first();
                
            } while ($existingToken); 
            db::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);
    
            Mail::to($request->email)->send(new \App\Mail\ForgotPassword($token, $request->email));

            return back()->with('status', 'Reset link has been sent to your email.');
            
            // return redirect()->route('customer.forgotPassword.form')->with('success', 'We have send an email to reset password. ');
        }

        // return back()->with('success', 'Verification email has been resent successfully.');
    }

    
    public function showResetPasswordForm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');
    
        $reset = DB::table('password_resets')->where('email', $email)->where('token', $token)->first();
    
        if (!$reset || now()->diffInMinutes($reset->created_at) > 60) {
            return redirect()->route('customer.forgotPassword.form')
            ->with('error', 'The link you used is either expired or invalid. Please request a new password reset.');
        }
    
        return view('auth.reset-password');
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'token' => 'required',
            'password' => [
                'required',
                'string',
                'min:4',
                'max:39',
                'regex:/^[a-zA-Z0-9_\-@.]+$/',
                function ($attribute, $value, $fail) {
                    $lower = strtolower($value);

                    if (preg_match('/(.)\1{3,}/', $lower)) {
                        return $fail("The $attribute must not contain repeated characters.");
                    }

                    $sequence = 'abcdefghijklmnopqrstuvwxyz0123456789';
                    for ($i = 0; $i <= strlen($sequence) - 4; $i++) {
                        if (strpos($lower, substr($sequence, $i, 4)) !== false) {
                            $fail("The $attribute must not contain sequential characters.");
                            break;
                        }
                    }
                    // $sequences = ['abcdefghijklmnopqrstuvwxyz', '0123456789'];
                    // foreach ($sequences as $seq) {
                    //     for ($i = 0; $i <= strlen($seq) - 4; $i++) {
                    //         if (strpos($lower, substr($seq, $i, 4)) !== false) {
                    //             return $fail("The $attribute must not contain sequential characters.");
                    //         }
                    //     }
                    // }
                }
            ]

        ]);

        $reset = DB::table('password_resets')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();
        if (!$reset) {
            return redirect()->back()
            ->with('error', 'The link you used is either expired or invalid. Please request a new password reset.');
        }

        $customer = Customer::where('email', $request->email)->first();

        // Call VoIP API
        $apiUrl = config('my_app_settings.voip.api_url');
        $response = Http::get($apiUrl, [
            'command' => 'resetpassword',
            'username' => config('my_app_settings.voip.username'),
            'password' => config('my_app_settings.voip.password'),
            'customer' => $customer->username,
            'newcustomerpassword' => $request->password,
        ]);

        $xml = simplexml_load_string($response);

        if ($xml && $xml->Result == 'Success') {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return redirect()->route('customer.login.form')
                            ->with('success', 'Your password has been reset successfully. You can now log in.');
        } else {
            // return redirect()->route('customer.forgotPassword.form')
            //                 ->withErrors(['email' => 'There was an error resetting your password. Please try again.']);
            return back()->withErrors(['error' => 'Could not reset password. Please try again.']);
        }

        // if (str_contains($response, 'OK')) {
        //     DB::table('password_resets')->where('email', $request->email)->delete();
        //     return redirect()
        //     ->route('customer.login.form')
        //     ->with('success', 'Your password has been reset successfully. You can now log in.');
        
        // }

        // return back()->withErrors(['error' => 'Could not reset password. Please try again.']);
    }

    


}
