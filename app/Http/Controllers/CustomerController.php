<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;




class CustomerController extends Controller
{
    //

    // public function dashboard()
    // {

    //     try {
    //         if (!session()->has('customer_password')) {
    //             throw new \Exception('Password not found in session.');
    //         }

    //         $password = Crypt::decryptString(session('customer_password'));

    //         // باقي الداشبورد مع بيانات المستخدم، وأنت تقدر تستخدم $password متى ما احتجته

    //         return view('customer.dashboard', compact('password'));
    //     } catch (\Exception $e) {
    //         // إذا حصل خطأ في فك التشفير أو السيشن مفقودة
    //         Auth::guard('customer')->logout();
    //         session()->forget('customer_password');
    //         return redirect()->route('customer.login.form')
    //             ->with('error', 'Session expired or invalid, please log in again.');
    //     }

    //     $userName = Auth::guard('customer')->user()->username;
    //     $apiResponse = Http::get(config('my_app_settings.voip.api_url'), [
    //         'command' => 'getuserinfo',
    //         'username' => config('my_app_settings.voip.username'),
    //         'password' => config('my_app_settings.voip.password'),
    //         'customer' => $userName,
    //         'customerpassword' => $password,
    //     ]);

    //     $xml = simplexml_load_string($apiResponse->body());


    //     $data = [
    //         'customer'         => (string) $xml->Customer,
    //         'balance'          => (float) $xml->Balance,
    //         'specific_balance' => (float) $xml->SpecificBalance,
    //         'blocked'          => (string) $xml->Blocked === 'True',
    //         'email'            => (string) $xml->EmailAddress,
    //         'phone'            => (string) $xml->GeocallCLI,
    //     ];

    //     $balance = [
    //         'total' => (float) $xml->Balance,
    //         'specific' => $xml->SpecificBalance,
    //     ];
            

    //     // Fake customer data
    //     // $user = (object)[
    //     //     'username' => 'jane_doe92',
    //     //     'email' => 'jane.doe92@example.com',
    //     //     'phone_number' => '+201234567890',
    //     //     'timezone' => 'Africa/Cairo',
    //     // ];

    //     // Fake balance data
    //     // $balance = [
    //     //     'total' => 250.75,
    //     //     'specific' => 100.00,
    //     // ];

    //     // Fake call history (last 5 calls)
    //     $recentCalls = [
    //         [
    //             'datetime' => '2025-05-14 14:32',
    //             'number' => '+201112223334',
    //             'duration' => '3m 45s',
    //             'cost' => 1.50,
    //         ],
    //         [
    //             'datetime' => '2025-05-14 09:15',
    //             'number' => '+201556677889',
    //             'duration' => '2m 10s',
    //             'cost' => 0.90,
    //         ],
    //         [
    //             'datetime' => '2025-05-13 18:50',
    //             'number' => '+201998877665',
    //             'duration' => '5m 00s',
    //             'cost' => 2.00,
    //         ],
    //         [
    //             'datetime' => '2025-05-13 12:05',
    //             'number' => '+201223344556',
    //             'duration' => '1m 30s',
    //             'cost' => 0.60,
    //         ],
    //         [
    //             'datetime' => '2025-05-12 20:40',
    //             'number' => '+201334455667',
    //             'duration' => '4m 20s',
    //             'cost' => 1.80,
    //         ],
    //     ];


    //             return view('customer.dashboard', compact('user', 'balance', 'recentCalls'));

    //     // return view('customer.dashboard', compact('balance', 'specificBalance', 'recentCalls'));

    //     // return view('customer.dashboard');
    // }
public function dashboard()
{
    try {

        if (!session()->has('customer_password')) {
            // كأنه لسه ما دخلش تسجيل دخول صح
            Auth::guard('customer')->logout();
            return redirect()->route('customer.login.form')
                ->with('error', 'Please log in to access your dashboard.');
        }

        // فك تشفير الباسورد
        $password = Crypt::decryptString(session('customer_password'));

        // dd('is decrypyed');

        $Userr = Auth::guard('customer')->user();

        $firstName = $Userr->first_name;
        $lastName = $Userr->last_name;


        // dd($user);

        // اسم المستخدم من السيشن
        $userName = Auth::guard('customer')->user()->username;

        // dd($userName);

        // طلب بيانات المستخدم من API
        $apiResponse = Http::get(config('my_app_settings.voip.api_url'), [
            'command' => 'getuserinfo',
            'username' => config('my_app_settings.voip.username'),
            'password' => config('my_app_settings.voip.password'),
            'customer' => $userName,
            'customerpassword' => $password,
        ]);

        // dd($apiResponse->ok());


        if (!$apiResponse->ok()) {
            throw new \Exception('Failed to retrieve user info from API.');
        }

        $xml = simplexml_load_string($apiResponse->body());

        // لو XML فاضي أو غير مفهوم
        if (!$xml || empty($xml->Customer)) {
            throw new \Exception('Invalid XML response.');
        }

        // dd($Userr->email);
        

        // بيانات المستخدم الحقيقية
        $user = [
            'username' => (string) $xml->Customer,
            'email' =>  $Userr->email,
            'phone_number' => $Userr->phone_number,
            'timezone' => $Userr->timezone, // مؤقتًا، لو عندك من API عدله
        ];

        // dd($user);


        // الرصيد
        $balance = [
            'total' => (float) $xml->Balance,
            'specific' => (float) $xml->SpecificBalance,
        ];

        // لسه مكالمات وهمية (زي ما طلبت)
        $recentCalls = [
            [
                'datetime' => '2025-05-14 14:32',
                'number' => '+201112223334',
                'duration' => '3m 45s',
                'cost' => 1.50,
            ],
            [
                'datetime' => '2025-05-14 09:15',
                'number' => '+201556677889',
                'duration' => '2m 10s',
                'cost' => 0.90,
            ],
            [
                'datetime' => '2025-05-13 18:50',
                'number' => '+201998877665',
                'duration' => '5m 00s',
                'cost' => 2.00,
            ],
            [
                'datetime' => '2025-05-13 12:05',
                'number' => '+201223344556',
                'duration' => '1m 30s',
                'cost' => 0.60,
            ],
            [
                'datetime' => '2025-05-12 20:40',
                'number' => '+201334455667',
                'duration' => '4m 20s',
                'cost' => 1.80,
            ],
        ];

        // عرض الداشبورد
        return view('customer.dashboard', compact('user', 'balance', 'recentCalls' ,'password'));

    } catch (\Exception $e) {
        // أي خطأ => تسجيل خروج وإعادة التوجيه
        Auth::guard('customer')->logout();
        session()->forget('customer_password');
        return redirect()->route('customer.login.form')
            ->with('error', 'We couldn’t load your dashboard. Please log in again to continue.');
            // ->with('error', 'Session expired or invalid, please log in again.');
    }
}

}
