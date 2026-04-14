<?php

namespace Database\Seeders;

use App\Models\Masjid;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MasjidSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        $masjidPayloads = [
            [
                'nama' => 'Masjid Al-Falah',
                'code' => 'alfalah',
                'alamat' => 'Jalan Kebun Bunga 12, Seksyen 7',
                'daerah' => 'Petaling',
                'negeri' => 'Selangor',
                'no_pendaftaran' => 'SGR-MF-ALF-001',
                'tarikh_daftar' => $today->copy()->subYears(11)->toDateString(),
                'status' => 'active',
                'subscription_status' => 'active',
                'subscription_expiry' => $today->copy()->addMonths(8)->endOfDay(),
            ],
            [
                'nama' => 'Masjid Ar-Rahman',
                'code' => 'arrahman',
                'alamat' => 'Jalan Damai 4, Bandar Baru',
                'daerah' => 'Klang',
                'negeri' => 'Selangor',
                'no_pendaftaran' => 'SGR-MF-ARR-002',
                'tarikh_daftar' => $today->copy()->subYears(9)->toDateString(),
                'status' => 'active',
                'subscription_status' => 'active',
                'subscription_expiry' => $today->copy()->addMonths(4)->endOfDay(),
            ],
            [
                'nama' => 'Masjid An-Nur',
                'code' => 'annur',
                'alamat' => 'Persiaran Ilmu 18, Presint Komuniti',
                'daerah' => 'Sepang',
                'negeri' => 'Selangor',
                'no_pendaftaran' => 'SGR-MF-ANN-003',
                'tarikh_daftar' => $today->copy()->subYears(7)->toDateString(),
                'status' => 'active',
                'subscription_status' => 'expired',
                'subscription_expiry' => $today->copy()->subDays(15)->endOfDay(),
            ],
        ];

        foreach ($masjidPayloads as $payload) {
            Masjid::query()->updateOrCreate(
                ['code' => $payload['code']],
                $payload
            );
        }
    }
}
