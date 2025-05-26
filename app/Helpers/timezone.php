<?php

if (! function_exists('iana_to_windows')) {
    function iana_to_windows($ianaTimezone)
    {
        $map = config('timezones.iana_to_windows');
        return $map[$ianaTimezone] ?? null;
    }
}

if (! function_exists('windows_to_iana')) {
    function windows_to_iana($windowsTimezone)
    {
        $map = config('timezones.windows_to_iana');
        return $map[$windowsTimezone] ?? null;
    }
}