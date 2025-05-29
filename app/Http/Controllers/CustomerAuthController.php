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
use Illuminate\Validation\Rule;
use App\Helpers\TimeZoneHelper;







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


        $windowsName = TimeZoneHelper::getWindowsFromIana($request->timezone); 
        
        // dd(email_enabled());


        try {
            DB::beginTransaction();
    
            // 1. Store customer locally
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => email_enabled() ? $request->email : null,
                // 'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_full,
                'timezone' => $windowsName,
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
                    'tariffrate' => config('my_app_settings.traiffrate'),
                    'country' => $dialCode,
                    'timezone' => $windowsName,
                ]
            );

            $xml = simplexml_load_string($createCustomerApiResponse->body());

            // dd($createCustomerApiResponse, $xml ,'$xml->Result :'.(string) $xml->Result ,'(!$xml || (string) $xml->Result !== Success) :'.(!$xml || (string) $xml->Result !== 'Success') ,'$request->phone :'.$request->phone,'urlencode($request->phone :'. urlencode($request->phone));

            // if (!$xml || (string) $xml->Result !== 'Success') {
            if (!$xml || !isset($xml->CustomerLoginName)) {

                $customer->delete();
                DB::rollBack();
            
                return back()->with(
                    'error' , 'This username might already be taken. Please choose another one.',
                );
            }
            
            if (email_enabled()) {

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
            
            }


            DB::commit();
            return redirect()->route('customer.login.form')->with('success', 'Your account has been created. You can now log in.');


    
    
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
            return redirect()->route('customer.register.form')->with('error', 'We couldn’t find your email address. Please register first.');
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

        
        if (!$customer) {
            // حاول تحقق من الـ API
            $apiResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'validateuser',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $request->username,
                'customerpassword' => $request->password,
            ]);

            $xml = simplexml_load_string($apiResponse->body());

            if ($xml && $xml->Result == 'Success') {
                // جلب بيانات المستخدم من API (مثلاً عبر أمر getuserinfo)
                $userInfoResponse = Http::get(config('my_app_settings.voip.api_url'), [
                    'command' => 'getuserinfo',
                    'username' => config('my_app_settings.voip.username'),
                    'password' => config('my_app_settings.voip.password'),
                    'customer' => $request->username,
                    'customerpassword' => $request->password,
                ]);

                if ($userInfoResponse->ok()) {
                    $userInfoXml = simplexml_load_string($userInfoResponse->body());

                    // dd($userInfoXml);

                    $userFull = (string) $userInfoXml->Customer; 
                    $username = explode('*', $userFull)[0];

                    $email =(string)$userInfoXml->EmailAddress ?? null;


                    $customer = Customer::create([
                        'first_name' => null,
                        'last_name' => null,
                        'username' => $username,
                        'email' => email_enabled() ? $request->email : null,
                        // 'email' => $email,
                        'phone_number' => null,
                        'is_active' => false,
                        'email_verified_at' => null,
                    ]);

                    session(['allow_complete_registration' => true]);


                    return redirect()->route('customer.complete-registration', ['username' => $username ,'email' => $email ])
                        ->with('info', 'Please complete your registration.');
                }
            }

            return redirect()->route('customer.login.form')->with('error', 'Incorrect username or password.');
        }
    

        if (email_enabled()) {
            
            // 2. تحقق من تفعيل الحساب
            if (! $customer->is_active || !$customer->email_verified_at) {
                return back()->withErrors(['msg' => 'Your account is not activated yet. Please check your email.']);
            }
        
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

        // Check block status and record the log of login

        $ip = $request->header('CF-Connecting-IP') ??
        $request->header('X-Forwarded-For') ??
        $request->ip();

        $xml = simplexml_load_string($apiResponse);

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
            }

            try {

                $key = config('my_app_settings.ipstack.access_key');
                $url = "http://api.ipstack.com/{$ip}?access_key={$key}";

                $geoResponse = Http::get($url);
                if ($geoResponse->ok()) {
                    $data = $geoResponse->json();
                    $country = $data['country_name'] ?? 'Unknown';
                }
            } catch (\Exception $e) {
                $country = 'Unknown';
            }

            // سجل اللوج بغض النظر
            LoginLog::create([
                'user_id' => $customer->id,
                'username' => $customer->username,
                'ip_address' => $ip,
                'login_time' => now(),
                'status' => $status,
                'country' => $country,
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
            // 'email' => 'required|email|exists:customers,email',
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
        // ->where('email', $request->email)
        ->where('token', $request->token)
        ->first();
        if (!$reset) {
            return redirect()->back()
            ->with('error', 'The link you used is either expired or invalid. Please request a new password reset.');
        }

        $customer = Customer::where('email', $reset->email)->first();

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

    
    public function showCompleteRegistrationForm(Request $request)
    {

        if (!session('allow_complete_registration')) {
            abort(403, 'Unauthorized access');
        }

        session()->forget('allow_complete_registration');


        
        $username = $request->query('username');
        $email = $request->query('email');

        

        return view('auth.complete-registration', compact('username', 'email'));
        // return view('auth.complete-registration');
    }

    public function CompleteRegistration(Request $request)
    {

        $windowsName = TimeZoneHelper::getWindowsFromIana($request->timezone); 
        $customer = Customer::where('username', $request->username)->first();

        $emailRules = ['email', Rule::unique('customers', 'email')->ignore($customer->id)];
        if (email_enabled()) {
            array_unshift($emailRules, 'required'); 
        } else {
            array_unshift($emailRules, 'nullable');
        }

        $request->validate([
            'first_name' => 'required|string|max:50|regex:/^[\pL\s\-]+$/u',
            'last_name' => 'required|string|max:50|regex:/^[\pL\s\-]+$/u',
            'email' => $emailRules,

            // 'email' => [
            //     'required', 'email',
            //     Rule::unique('customers', 'email')->ignore($customer->id)
            // ],
            'phone_full' => [
                'required',
                'regex:/^\+?[1-9][0-9]{6,15}$/',
                Rule::unique('customers', 'phone_number')->ignore($customer->id),
                function ($attribute, $value, $fail) {
                    if (preg_match('/(.)\1{5,}/', $value)) {
                        $fail("The phone number must not contain repeated digits.");
                    }
                }
            ],
            'country_code' => 'nullable|string|size:2',
            'timezone' => 'nullable|string|max:50',
            'ip_address' => 'nullable|ip',
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a valid string.',
            'first_name.max' => 'First name cannot exceed 50 characters.',
            'first_name.regex' => 'First name can only contain letters, spaces, and hyphens.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a valid string.',
            'last_name.max' => 'Last name cannot exceed 50 characters.',
            'last_name.regex' => 'Last name can only contain letters, spaces, and hyphens.',
            'phone_full.required' => 'Phone number is required.',
            'phone_full.regex' => 'Please enter a valid international phone number (e.g., +441234567890).',
            'phone_full.unique' => 'This phone number is already in use.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
        ]);


        // 1. Update customer locally without email
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_full,
            'timezone' => $windowsName,
            'country_code' => $request->country_code,
        ];

        // if enabled email
        if (email_enabled()) {
            $updateData['email'] = $request->email;
            $updateData['email_verified_at'] = null;
        }

        $customer->update($updateData);

        // 2. if enabled eamil Generate email verification token
        if (email_enabled()) {
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

            return redirect()->route('customer.verify.notice');
        } else {


            return redirect()->route('customer.login.form')->with('success', 'Profile updated successfully.You can login ');
        }


        // // 1. Store customer locally
        // $customer->update([
        //     'first_name' => $request->first_name,
        //     'last_name' => $request->last_name,
        //     'email' => $request->email,
        //     'phone_number' => $request->phone_full,
        //     'timezone' => $request->timezone,
        //     'country_code' => $request->country_code,
        //     'email_verified_at' => null, // ضروري لإجباره على التفعيل
        // ]);
            
        // // 2. Generate email verification token
        // do {
        //     $token = Str::random(64); 
        
        //     $existingToken = EmailVerification::where('token', $token)->first();

        // } while ($existingToken); 
            
        // EmailVerification::create([
        //     'email' => $request->email,
        //     'token' => $token,
        //     'expires_at' => Carbon::now()->addMinutes(30),
        // ]);

        // // 5. Send verification email
        // Mail::to($request->email)->send(new \App\Mail\VerifyCustomerEmail($token, $request->email));

        // return redirect()->route('customer.verify.notice');

    
    }

    

    


}
