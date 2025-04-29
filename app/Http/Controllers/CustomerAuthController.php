<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\EmailVerification;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;






class CustomerAuthController extends Controller
{
    //

    public function showRegisterForm()
    {
        return view('auth.register');
    }


    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:customers,email',
            'username' => 'required|unique:customers,username|regex:/^[a-zA-Z0-9_\-\.@]+$/',
            'phone' => [
                'required',
                'regex:/^\+?[1-9][0-9]{6,15}$/',
                function ($attribute, $value, $fail) {
                    if (preg_match('/(.)\1{3,}/', $value)) {
                        $fail("The phone number must not contain repeated digits.");
                    }
                }
            ],
            'customerpassword' => [
                'required',
                'string',
                'min:4',
                'max:39',
                'regex:/^[a-zA-Z0-9_\-@.]+$/',
                function ($attribute, $value, $fail) {
                    $lower = strtolower($value);
                    $sequence = 'abcdefghijklmnopqrstuvwxyz0123456789';
                    for ($i = 0; $i <= strlen($sequence) - 4; $i++) {
                        if (strpos($lower, substr($sequence, $i, 4)) !== false) {
                            $fail("The $attribute must not contain sequential characters.");
                            break;
                        }
                    }
                }
            ],
        ]);
    
        try {
            DB::beginTransaction();
    
            // 1. Store customer locally
            $customer = Customer::create([
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone,
                'country_code' => $request->country_code,
                'is_active' => false,
            ]);
    
            // 2. Store customer in API
            $createCustomerApiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
                'command' => 'createcustomer',
                'username' => env('VOIP_RESELLER_USERNAME'),
                'password' => env('VOIP_RESELLER_PASSWORD'),
                'customer' => $request->username,
                'customerpassword' => $request->customerpassword,
                'geocallcli' => urlencode($request->phone),
            ]);
    
            $xml = simplexml_load_string($createCustomerApiResponse->body());

            if (!$xml || (string) $xml->Result !== 'Success') {

                $customer->delete();
                DB::rollBack();
            
                return back()->withErrors([
                    'username' => 'This username might already be taken on our provider. Please choose another one.',
                ])->withInput();
            }
            
    
            // 3. Temporarily block the customer 
            $changeuserinfoApiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
                'command' => 'changeuserinfo',
                'username' => env('VOIP_RESELLER_USERNAME'),
                'password' => env('VOIP_RESELLER_PASSWORD'),
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
    
            return back()->withErrors([
                'msg' => 'An error occurred during registration: ' . $e->getMessage(),
            ])->withInput();
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
            return redirect()->route('register')->withErrors(['msg' =>  'We couldn’t find your email address. Please register first.']);
        }

        // If the email is already verified and the account is active, redirect to login
        if ($customer->is_active && $customer->email_verified_at) {
            return redirect()->route('login')->with('message', 'Your email is already verified. Please log in.');
        }

        $emailVerification = EmailVerification::where('token', $token)->first();

        // If the token is invalid or doesn't match the email, show error
        if (!$emailVerification || $emailVerification->email !== $email) {
            return redirect()->route('login')->withErrors(['msg' => 'Invalid or expired verification link.']);
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

            return redirect()->route('customer.verify.resend')->with('message', 'The verification link has expired. Please enter your email to receive a new one.');

        }

        //// 2.Unblock the customer in the external VoIP system
        try {

            $changeuserinfoApiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
                'command' => 'changeuserinfo',
                'username' => 'callsland',
                'password' => 'trafficzone',
                'customer' => $customer->username,  
                'customerblocked' => 'false', 
            ]);

            if ($changeuserinfoApiResponse->failed()) {
                return redirect()->route('login')->withErrors(['msg' => 'We couldn’t connect to the verification service. Please try again later.']);
            }
            
            $responseXml = simplexml_load_string($changeuserinfoApiResponse->body());
            // Check if the response contains Result and Reason
            if ($responseXml->Result == 'Failed') {
                $reason = (string)$responseXml->Reason; // Get the reason for failure
                
                // Custom messages based on the reason
                if ($reason == 'Unknown customer') {
                    return redirect()->route('login')->withErrors(['msg' => 'We couldn’t find your account. Please check the information you entered.']);
                }
                
                // If the request failed partially
                return redirect()->route('login')->withErrors(['msg' => 'We couldn’t connect to the verification service. Please try again later.']);
            }


        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['msg' =>'We couldn’t connect to the verification service. Please try again later.']);
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

            return redirect()->route('login')->with('message', 'Your email has been successfully verified. You can now log in.');

        } catch (\Exception $e) {
            DB::rollBack();  
            return redirect()->route('login')->withErrors(['msg' => 'Something went wrong. Please try again later.']);
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
            return redirect()->route('customer.login')->with('message', 'Your email is already verified. You can login now.');
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

        Mail::to($request->email)->send(new VerifyCustomerEmail($token, $request->email));

        // return redirect()->route('customer.login')->with('message', 'A new verification link has been sent to your email.');
        

        return back()->with('success', 'Verification email has been resent successfully.');
    }

    
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // التحقق من صحة المدخلات (البريد الإلكتروني وكلمة المرور)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // محاولة تسجيل الدخول
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // التحقق إذا كان البريد الإلكتروني مفعلًا
            if (!$user->email_verified_at) {
                // إذا لم يكن مفعلًا، نوجهه إلى صفحة التنبيه مع رسالة
                return redirect()->route('customer.verify.resend')->with('message', 'Your email is not verified. Please verify your email.');
            }

            // إذا كان مفعلًا، نسمح له بالدخول
            return redirect()->route('customer.dashboard');
        } else {
            return redirect()->route('customer.login')->withErrors(['msg' => 'Invalid credentials.']);
        }
    }




}
