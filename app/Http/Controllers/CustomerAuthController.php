<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerAuthController extends Controller
{
    //

    public function showRegisterForm()
    {
        return view('auth.customer-register');
    }

    public function register(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|email|unique:customers,email',
        //     'username' => 'required|unique:customers,username',
        //     'phone' => 'required',
        //     'country_code' => 'required',
        //     'customerpassword' => 'required|min:6',
        // ]);

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
        
            'country_code' => 'required|numeric|between:0,999',
        
            'customerpassword' => [
                'required',
                'string',
                'min:4',
                'max:39',
                'regex:/^[a-zA-Z0-9_\-@.]+$/',
                function ($attribute, $value, $fail) {
                    // Check for repeated patterns (e.g., 'aaaa', '1111')
                    if (preg_match('/(.)\1{3,}/', $value)) {
                        $fail("The $attribute must not contain repeated characters.");
                    }
        
                    // Check for incremental sequences (e.g., 1234, abcd)
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
        
        dd($request);


        // 1. تخزين بيانات العميل في جدول customers
        $customer = Customer::create([
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
            'country_code' => $request->country_code,
            'is_verified' => false,
        ]);

        // 2. إرسال البيانات إلى API VoIP لتسجيله
        $apiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
            'command' => 'addcustomer',
            'username' => config('voip.username'),
            'password' => config('voip.password'),
            'customer' => $request->username,
            'customerpassword' => $request->customerpassword,
        ]);

        if ($apiResponse->failed() || !str_contains($apiResponse->body(), 'Success')) {
            $customer->delete();
            return back()->withErrors(['msg' => 'Something went wrong with API, please try again later.']);
        }

        // 3. Block العميل مؤقتًا من الـ API
        Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
            'command' => 'changeuserinfo',
            'username' => config('voip.username'),
            'password' => config('voip.password'),
            'customer' => $request->username,
            'customerblocked' => 'true',
        ]);

        // 4. إنشاء توكن توثيق الإيميل
        $token = Str::random(64);
        EmailVerification::create([
            'email' => $request->email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        // 5. إرسال الإيميل
        Mail::to($request->email)->send(new \App\Mail\VerifyCustomerEmail($token));

        return redirect()->route('customer.login.form')->with('message', 'Check your email to verify your account.');
    }
}
