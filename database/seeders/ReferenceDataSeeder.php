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
                'alamat' => 'Jalan P8C/2, Presint 8, 62250 Putrajaya',
                'daerah' => 'Putrajaya',
                'negeri' => 'Wilayah Persekutuan',
                'no_pendaftaran' => 'WP-IMAM-001',
                'tarikh_daftar' => '2017-03-15',
            ],
            [
                'nama' => 'Masjid An-Nur Cyberjaya',
                'alamat' => 'Jalan Teknokrat 5, 63000 Cyberjaya',
                'daerah' => 'Sepang',
                'negeri' => 'Selangor',
                'no_pendaftaran' => 'SGR-IMAM-014',
                'tarikh_daftar' => '2019-08-01',
            ],
            [
                'nama' => 'Masjid Al-Falah Shah Alam',
                'alamat' => 'Jalan Plumbum 7/102, Seksyen 7, 40000 Shah Alam',
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
            ['nama_akaun' => 'Bank Operasi', 'jenis' => 'bank', 'no_akaun' => '1400' . $masjidId . '001234', 'nama_bank' => 'Maybank Islamic', 'status_aktif' => true],
            ['nama_akaun' => 'Bank Program Komuniti', 'jenis' => 'bank', 'no_akaun' => '1400' . $masjidId . '009876', 'nama_bank' => 'Bank Islam', 'status_aktif' => true],
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
            ['kod' => 'DERMA-IND', 'nama_sumber' => 'Derma Individu', 'jenis' => 'derma', 'aktif' => true],
            ['kod' => 'SUMB-JMT', 'nama_sumber' => 'Sumbangan Jumaat', 'jenis' => 'derma', 'aktif' => true],
            ['kod' => 'DERMA-TAB', 'nama_sumber' => 'Derma Tabung Masjid', 'jenis' => 'derma', 'aktif' => true],
            ['kod' => 'SUMB-RAM', 'nama_sumber' => 'Sumbangan Ihya Ramadan', 'jenis' => 'derma', 'aktif' => true],
            ['kod' => 'WAKAF-BINA', 'nama_sumber' => 'Wakaf Pembinaan', 'jenis' => 'wakaf', 'aktif' => true],
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
            ['kod' => 'UTIL', 'nama_kategori' => 'UTILITI', 'aktif' => true],
            ['kod' => 'BAIKPULIH', 'nama_kategori' => 'BAIK PULIH', 'aktif' => true],
            ['kod' => 'PENCERAMAH', 'nama_kategori' => 'BAYARAN PENCERAMAH', 'aktif' => true],
            ['kod' => 'ELAUN', 'nama_kategori' => 'ELAUN & EMOLUMEN', 'aktif' => true],
            ['kod' => 'PERALATAN', 'nama_kategori' => 'PEMBELIAN PERALATAN', 'aktif' => true],
            ['kod' => 'SELENGGARA', 'nama_kategori' => 'PENYELENGGARAAN', 'aktif' => true],
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
            ['nama_tabung' => 'Tabung Ihya Ramadan 2026', 'aktif' => true],
            ['nama_tabung' => 'Wakaf Bangunan Masjid', 'aktif' => true],
            ['nama_tabung' => 'Tabung Kebajikan', 'aktif' => true],
            ['nama_tabung' => 'Tabung Pendidikan', 'aktif' => true],
            ['nama_tabung' => 'Tabung Operasi Masjid', 'aktif' => true],
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
            ['nama_program' => 'Kelas Fardu Ain Remaja', 'aktif' => true],
            ['nama_program' => 'Program Gotong-Royong Perdana 2023', 'aktif' => false],
        ];

        foreach ($programs as $program) {
            ProgramMasjid::query()->updateOrCreate(
                ['id_masjid' => $masjidId, 'nama_program' => $program['nama_program']],
                $program + ['id_masjid' => $masjidId]
            );
        }
    }
}
