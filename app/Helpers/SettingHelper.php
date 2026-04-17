<?php

use Illuminate\Support\Facades\DB;

if (! function_exists('setting')) {
    /**
     * Retrieve a system setting value, cached for 60 seconds.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return cache()->remember("setting_{$key}", 60, function () use ($key, $default): mixed {
            $value = DB::table('system_settings')->where('key', $key)->value('value');

            return $value ?? $default;
        });
    }
}
