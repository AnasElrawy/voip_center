<?php

return [

    'voip' => [
        'api_url' => env('VOIP_API_URL'),  
        'username' => env('VOIP_RESELLER_USERNAME'),
        'password' => env('VOIP_RESELLER_PASSWORD'),
    ],

    'ipstack' => [
        'access_key' => env ('IPSTACK_ACCESS_KEY'),
        'base_url' => env('IPSTACK_API_URL')
    ],

    'version' => env ('VERSION'),
    
];
