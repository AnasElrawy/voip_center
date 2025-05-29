<?php

namespace App\Helpers;

class TimeZoneHelper
{
    public static function getWindowsFromIana($iana)
    {
        $map = config('timezones.iana_to_windows');
        return $map[$iana] ?? null;
    }

    public static function getIanaFromWindows($windows)
    {
        static $reverse = null;
        if (!$reverse) {
            $reverse = array_flip(config('timezones.iana_to_windows'));
        }

        return $reverse[$windows] ?? null;
    }
}
