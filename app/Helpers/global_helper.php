<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('settings')) {
    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function settings($key, $default = null)
    {
        // Cache settings for 24 hours to reduce database queries
        // You can clear this cache when updating settings
        $settings = Cache::remember('app_settings', 60 * 24, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('get_weekend_days')) {
    /**
     * Get list of configured weekend days (lowercase english day names).
     * Sunday is always included by default.
     *
     * @return array
     */
    function get_weekend_days()
    {
        $raw = settings('weekend_days', '[]');
        $configured = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);

        // Sunday is always a weekend day
        if (!in_array('sunday', $configured)) {
            $configured[] = 'sunday';
        }

        return array_map('strtolower', $configured);
    }
}

if (!function_exists('is_weekend_day')) {
    /**
     * Check if a given date is a weekend day (Sunday, or configured Friday/Saturday).
     *
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    function is_weekend_day($date)
    {
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        $dayName = strtolower($carbon->englishDayOfWeek); // e.g. 'friday', 'saturday', 'sunday'

        return in_array($dayName, get_weekend_days());
    }
}

if (!function_exists('get_weekend_day_label')) {
    /**
     * Get uppercase label for weekend day in Indonesian (e.g. 'JUMAT', 'SABTU', 'MINGGU').
     *
     * @param \Carbon\Carbon|string $date
     * @return string
     */
    function get_weekend_day_label($date)
    {
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return strtoupper($carbon->translatedFormat('l'));
    }
}
