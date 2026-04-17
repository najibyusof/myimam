<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key'   => 'landing_page_mode',
                'value' => 'cms',
            ],
        ];

        foreach ($defaults as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
