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

        // $request->validate([
        //     'email' => 'required|email|unique:customers,email',
        
        //     'username' => 'required|unique:customers,username|regex:/^[a-zA-Z0-9_\-\.@]+$/',
        
        //     'phone' => [
        //         'required',
        //         'regex:/^\+?[1-9][0-9]{6,15}$/',
        //         function ($attribute, $value, $fail) {
        //             if (preg_match('/(.)\1{3,}/', $value)) {
        //                 $fail("The phone number must not contain repeated digits.");
        //             }
        //         }
        //     ],
        
        //     'country_code' => 'required|numeric|between:0,999',
        
        //     'customerpassword' => [
        //         'required',
        //         'string',
        //         'min:4',
        //         'max:39',
        //         'regex:/^[a-zA-Z0-9_\-@.]+$/',
        //         function ($attribute, $value, $fail) {
        //             // Check for repeated patterns (e.g., 'aaaa', '1111')
        //             if (preg_match('/(.)\1{3,}/', $value)) {
        //                 $fail("The $attribute must not contain repeated characters.");
        //             }
        
        //             // Check for incremental sequences (e.g., 1234, abcd)
        //             $lower = strtolower($value);
        //             $sequence = 'abcdefghijklmnopqrstuvwxyz0123456789';
        
        //             for ($i = 0; $i <= strlen($sequence) - 4; $i++) {
        //                 if (strpos($lower, substr($sequence, $i, 4)) !== false) {
        //                     $fail("The $attribute must not contain sequential characters.");
        //                     break;
        //                 }
        //             }
        //         }
        //     ],
        // ]);
        
        // dd($request);


        // // 1. تخزين بيانات العميل في جدول customers
        // $customer = Customer::create([
        //     'email' => $request->email,
        //     'username' => $request->username,
        //     'phone' => $request->phone,
        //     'country_code' => $request->country_code,
        //     'is_verified' => false,
        // ]);

        // env('VOIP_RESELLER_USERNAME'),
        // 2.
        // $createcustomerApiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
        //     'command' => 'createcustomer',
        //     'username' => 'callsland',
        //     'password' => 'trafficzone',
        //     'customer' => 'Anas1',
        //     'customerpassword' => '123456789',
        // ]);

        // $apiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
        //     'command' => 'createcustomer',
        //     'username' => 'callsland',
        //     'password' => 'trafficzone',
        //     'customer' => 'Anas1',
        //     'customerpassword' => 'securePass123',
        //     'geocallcli' => '%2B201090388845',
        //     'tariffrate' => '123', // مثال لمعدل التعريفة
        //     'country' => '826', // كود الدولة (مثال: 826 لبريطانيا)
        //     'timezone' => 'GMT Standard Time',
        // ]);

        // logger($apiResponse->body()); // أو
        // dd($apiResponse->body());
        // if ($apiResponse->failed() || !str_contains($apiResponse->body(), 'Success')) {
        //     // $customer->delete();
        //     return back()->withErrors(['msg' => 'Something went wrong with API, please try again later.']);
        // }

        // 3. Block العميل مؤقتًا من الـ API
        // $changeuserinfoApiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
        //     'command' => 'changeuserinfo',
        //     'username' => 'callsland',
        //     'password' => 'trafficzone',
        //     'customer' => 'Anas1',
        //     'customerblocked' => 'true',
        // ]);




        // logger($changeuserinfoApiResponse->body()); // أو
        // dd($changeuserinfoApiResponse->body());

        // 4. إنشاء توكن توثيق الإيميل
        // $token = Str::random(64);
        // EmailVerification::create([
        //     'email' => 'anaselrawy99@gmail.com',
        //     'token' => $token,
        //     'expires_at' => Carbon::now()->addMinutes(30),
        // ]);

        // 5. إرسال الإيميل
        // Mail::to('anaselrawy99@gmail.com')->send(new \App\Mail\VerifyCustomerEmail($token,'anaselrawy99@gmail.com'));

        // return back()->with('message', 'Check your email to verify your account.');
    }


    
public function verifyEmail(Request $request)
{
    // 1. الحصول على التوكن والبريد الإلكتروني من الرابط
    $token = $request->query('token');
    $email = $request->query('email');

    // 2. التحقق من وجود التوكن والبريد الإلكتروني في قاعدة البيانات
    $emailVerification = EmailVerification::where('email', $email)
                                         ->where('token', $token)
                                         ->first();

                                        //  dd($token,$email,$emailVerification);

    // if (!$emailVerification) {
    //     return redirect()->route('login')->withErrors(['msg' => 'Invalid or expired verification link.']);
    // }

    // // 3. التحقق من صلاحية التوكن (إذا انتهت صلاحية التوكن)
    // if (Carbon::now()->gt($emailVerification->expires_at)) {
    //     return redirect()->route('login')->withErrors(['msg' => 'The verification link has expired.']);
    // }

        //     $customer = Customer::create([
        //     'email' => 'anaselrawy99@gmail.com',
        //     'username' => 'Anas1',
        //     'phone_number' => '01090388845',
        //     'country_code' => '+20',
        //     'is_active' => false,
        // ]);


    // 4. إذا كانت كل الشروط صحيحة، قم بتفعيل الحساب
    DB::beginTransaction();
    try {
        // فعل الحساب أو المستخدم هنا
        // مثلا إذا كان لديك جدول users، يمكنك فعل ذلك
        $Customer = Customer::where('email', $email)->first();
        if ($Customer) {
            $Customer->email_verified_at = Carbon::now();
            $Customer->is_active = true;
            $Customer->save();
        }

         // 3. Block العميل مؤقتًا من الـ API
        $changeuserinfoApiResponse = Http::get('https://www.voipinfocenter.com/API/Request.ashx', [
            'command' => 'changeuserinfo',
            'username' => 'callsland',
            'password' => 'trafficzone',
            'customer' => 'Anas1',
            'customerblocked' => 'false',
        ]);


        // حذف التوكن بعد التحقق
        $emailVerification->delete();

        DB::commit();

        // 5. إعادة توجيه المستخدم إلى صفحة النجاح
        // return redirect()->route('login')->with('message', 'Your email has been successfully verified. You can now log in.');
    } catch (\Exception $e) {
        DB::rollBack();
        // return redirect()->route('login')->withErrors(['msg' => 'Something went wrong. Please try again later.']);
    }
}
}
