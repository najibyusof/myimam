<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MultiTenantDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            MasjidSeeder::class,
            UserSeeder::class,
            SubscriptionSeeder::class,
            FinanceSeeder::class,
            CmsSeeder::class,
        ]);
    }
}
