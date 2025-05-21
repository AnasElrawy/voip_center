<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;





class CustomerController extends Controller
{
        
    public function dashboard()
    {
        try {

            if (!session()->has('customer_password')) {
                Auth::guard('customer')->logout();
                return redirect()->route('customer.login.form')
                    ->with('error', 'Please log in to access your dashboard.');
            }

            $password = Crypt::decryptString(session('customer_password'));


            $Userr = Auth::guard('customer')->user();

            $firstName = $Userr->first_name;
            $lastName = $Userr->last_name;



            $userName = Auth::guard('customer')->user()->username;


            $apiResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'getuserinfo',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $userName,
                'customerpassword' => $password,
            ]);



            if (!$apiResponse->ok()) {
                throw new \Exception('Failed to retrieve user info from API.');
            }

            $xml = simplexml_load_string($apiResponse->body());

            // if (!$xml || empty($xml->Customer)) {
            //     throw new \Exception('Invalid XML response.');
            // }

            

            $user = [
                'username' => (string) $xml->Customer,
                'email' =>  $Userr->email,
                'phone_number' => $Userr->phone_number,
                'timezone' => $Userr->timezone,
            ];


            $balance = [
                'total' => (float) $xml->Balance,
                'specific' => (float) $xml->SpecificBalance,
            ];


            $callApiResponse = Http::get(config('my_app_settings.voip.api_url'), [
                'command' => 'calloverview',
                'username' => config('my_app_settings.voip.username'),
                'password' => config('my_app_settings.voip.password'),
                'customer' => $userName,
                'customerpassword' => $password,
                'recordcount' => 5,
            ]);

            $recentCalls = [];


            if ($callApiResponse->ok()) {
               
                $callsXml = simplexml_load_string($callApiResponse->body());
                
                if (!empty($callsXml->Calls) && isset($callsXml->Calls->Call)) {
                    foreach ($callsXml->Calls->Call as $call) {
                        $recentCalls[] = [
                            'datetime' => trim((string) $call['StartTime']),
                            'number' => (string) $call['Destination'],
                            'duration' => (string) $call['Duration'],
                            'cost' => (float) $call['Charge'],
                        ];
                    }
                }
            }

            return view('customer.dashboard', compact('user', 'balance', 'recentCalls' ,'password'));

        } catch (\Exception $e) {
            Auth::guard('customer')->logout();
            session()->forget('customer_password');
            return redirect()->route('customer.login.form')
                ->with('error', 'We couldn’t load your dashboard. Please log in again to continue.');
                // ->with('error', 'Session expired or invalid, please log in again.');
        }
    }


    public function callHistory(Request $request)
    {
        $userName = Auth::guard('customer')->user()->username;
        $password = Crypt::decryptString(session('customer_password'));

        $date = $request->filled('date')
        ? Carbon::parse($request->date)->format('Y-m-d H:i:s')
        : now()->format('Y-m-d H:i:s');

        $queryParams = [
            'command' => 'calloverview',
            'username' => config('my_app_settings.voip.username'),
            'password' => config('my_app_settings.voip.password'),
            'customer' => $userName,
            'customerpassword' => $password,
            'date' => $date ,
            'callid' => $request->callid ?? 0,
            'recordcount' => $request->recordcount ?? 10,
            'direction' => $request->direction ?? 'backward',
        ];

        $response = Http::get(config('my_app_settings.voip.api_url'), $queryParams);

        $calls = [];

        // if ($response->ok()) {
        //     $xml = simplexml_load_string($response->body());

        //     if (!empty($xml->Calls) && isset($xml->Calls->Call)) {
        //         $calls = collect($xml->Calls->Call)->map(function ($call) {
        //             return [
        //                 'start_time' => (string) $call['StartTime'],
        //                 'duration' => (string) $call['Duration'],
        //                 'destination' => (string) $call['Destination'],
        //                 'charge' => (string) $call['Charge'],
        //                 'callid' => (string) $call['CallID'],
        //             ];
        //         });
        //     }
        // }

        if ($response->ok()) {
      
            $callsXml = simplexml_load_string($response->body());
            
            if (!empty($callsXml->Calls) && isset($callsXml->Calls->Call)) {
                foreach ($callsXml->Calls->Call as $call) {
                    $calls[] = [
                        'start_time' => (string) $call['StartTime'],
                        'duration' => (string) $call['Duration'],
                        'destination' => (string) $call['Destination'],
                        'charge' => (string) $call['Charge'],
                        'callid' => (string) $call['CallID'],
                    ];
                }
            }
        }

        // dd($calls);
        // $calls = collect();

        // dd($response->body());


        return view('customer.callHistory', [
            'calls' => $calls,
            'filters' => $request->only(['date', 'callid', 'recordcount', 'direction']),
        ]);
    }

    

}
