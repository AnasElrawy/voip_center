<?php

if (!function_exists('email_enabled')) {
    function email_enabled(): bool
    {
        return filter_var(config('my_app_settings.email_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }
}


if (!function_exists('logo_image')) {
    function logo_image(): ?string
    {
        return config('my_app_settings.logo_image');
    }
}

if (!function_exists('layout_color')) {
    function layout_color(): string
    {
        return config('my_app_settings.layout_color', '#0d6efd');
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        return config('my_app_settings.symbol', '€');
    }
}

if (!function_exists('convert_amount')) {
    function convert_amount(float|int $amount = 1): float
    {
        $rate = (float) config('my_app_settings.rate', 1.0);
        return $amount * $rate;
    }
}