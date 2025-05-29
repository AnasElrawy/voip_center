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

    'traiffrate' => env('TRAIFF_RATE'),

    'email_enabled' => env('EMAIL_ENABLE'),

    'logo_image' => env('LOGO_IMAGE', null),
    'layout_color' => env('LAYOUT_COLOR', '#0d6efd'),

    'symbol' => env('CURRENCY_SYMBOL', '€'),
    'rate' => env('CONVERSION_RATE', 1.0),

    
];

