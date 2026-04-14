<?php

namespace Database\Seeders;

use App\Models\Akaun;
use App\Models\KategoriBelanja;
use App\Models\Masjid;
use App\Models\ProgramMasjid;
use App\Models\SumberHasil;
use App\Models\TabungKhas;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $masjids = [
            [
                'nama' => 'Masjid Al-Hidayah Putrajaya',
                'alamat' => 'Presint 8, Putrajaya',
                'daerah' => 'Putrajaya',
                'negeri' => 'Wilayah Persekutuan',
                'no_pendaftaran' => 'WP-IMAM-001',
                'tarikh_daftar' => '2017-03-15',
            ],
            [
                'nama' => 'Masjid An-Nur Cyberjaya',
                'alamat' => 'Jalan Teknokrat 5, Cyberjaya',
                'daerah' => 'Sepang',
                'negeri' => 'Selangor',
                'no_pendaftaran' => 'SGR-IMAM-014',
                'tarikh_daftar' => '2019-08-01',
            ],
            [
                'nama' => 'Masjid Al-Falah Shah Alam',
                'alamat' => 'Seksyen 7, Shah Alam',
                'daerah' => 'Petaling',
                'negeri' => 'Selangor',
                'no_pendaftaran' => 'SGR-IMAM-033',
                'tarikh_daftar' => '2015-11-22',
            ],
        ];

        foreach ($masjids as $masjidData) {
            $masjid = Masjid::query()->updateOrCreate(
                ['no_pendaftaran' => $masjidData['no_pendaftaran']],
                $masjidData
            );

            $this->seedAccounts($masjid->id);
            $this->seedRevenueSources($masjid->id);
            $this->seedExpenseCategories($masjid->id);
            $this->seedFunds($masjid->id);
            $this->seedPrograms($masjid->id);
        }
    }

    private function seedAccounts(int $masjidId): void
    {
        $accounts = [
            ['nama_akaun' => 'Tunai Utama', 'jenis' => 'tunai', 'no_akaun' => null, 'nama_bank' => null, 'status_aktif' => true],
            ['nama_akaun' => 'Bank Operasi', 'jenis' => 'bank', 'no_akaun' => '1400'.$masjidId.'001234', 'nama_bank' => 'Maybank Islamic', 'status_aktif' => true],
            ['nama_akaun' => 'Bank Program Komuniti', 'jenis' => 'bank', 'no_akaun' => '1400'.$masjidId.'009876', 'nama_bank' => 'Bank Islam', 'status_aktif' => true],
            ['nama_akaun' => 'Akaun Amanah Manual', 'jenis' => 'lain', 'no_akaun' => null, 'nama_bank' => null, 'status_aktif' => false],
        ];

        foreach ($accounts as $account) {
            Akaun::query()->updateOrCreate(
                ['id_masjid' => $masjidId, 'nama_akaun' => $account['nama_akaun']],
                $account + ['id_masjid' => $masjidId]
            );
        }
    }

    private function seedRevenueSources(int $masjidId): void
    {
        $sources = [
            ['kod' => 'DERMA-JMT', 'nama_sumber' => 'Derma Jumaat', 'jenis' => 'derma', 'aktif' => true],
            ['kod' => 'ONLINE', 'nama_sumber' => 'Sumbangan Online', 'jenis' => 'online', 'aktif' => true],
            ['kod' => 'SEWA', 'nama_sumber' => 'Sewaan Dewan', 'jenis' => 'sewaan', 'aktif' => true],
            ['kod' => 'INFAQ-KHAS', 'nama_sumber' => 'Infaq Khas', 'jenis' => 'infaq', 'aktif' => false],
        ];

        foreach ($sources as $source) {
            SumberHasil::query()->updateOrCreate(
                ['id_masjid' => $masjidId, 'kod' => $source['kod']],
                $source + ['id_masjid' => $masjidId]
            );
        }
    }

    private function seedExpenseCategories(int $masjidId): void
    {
        $categories = [
            ['kod' => 'UTIL', 'nama_kategori' => 'Utiliti & Operasi', 'aktif' => true],
            ['kod' => 'KEBAJIKAN', 'nama_kategori' => 'Kebajikan', 'aktif' => true],
            ['kod' => 'SELENGGARA', 'nama_kategori' => 'Penyelenggaraan', 'aktif' => true],
            ['kod' => 'PROGRAM', 'nama_kategori' => 'Program Komuniti', 'aktif' => true],
            ['kod' => 'ARKIB', 'nama_kategori' => 'Perbelanjaan Lama', 'aktif' => false],
        ];

        foreach ($categories as $category) {
            KategoriBelanja::query()->updateOrCreate(
                ['id_masjid' => $masjidId, 'kod' => $category['kod']],
                $category + ['id_masjid' => $masjidId]
            );
        }
    }

    private function seedFunds(int $masjidId): void
    {
        $funds = [
            ['nama_tabung' => 'Tabung Operasi', 'aktif' => true],
            ['nama_tabung' => 'Tabung Ramadan', 'aktif' => true],
            ['nama_tabung' => 'Dana Kecemasan', 'aktif' => true],
            ['nama_tabung' => 'Tabung Legacy', 'aktif' => false],
        ];

        foreach ($funds as $fund) {
            TabungKhas::query()->updateOrCreate(
                ['id_masjid' => $masjidId, 'nama_tabung' => $fund['nama_tabung']],
                $fund + ['id_masjid' => $masjidId]
            );
        }
    }

    private function seedPrograms(int $masjidId): void
    {
        $programs = [
            ['nama_program' => 'Kelas Tafsir Subuh', 'aktif' => true],
            ['nama_program' => 'Iftar Jamaie', 'aktif' => true],
            ['nama_program' => 'Klinik Komuniti', 'aktif' => true],
            ['nama_program' => 'Program Arkib 2023', 'aktif' => false],
        ];

        foreach ($programs as $program) {
            ProgramMasjid::query()->updateOrCreate(
                ['id_masjid' => $masjidId, 'nama_program' => $program['nama_program']],
                $program + ['id_masjid' => $masjidId]
            );
        }
    }
}
