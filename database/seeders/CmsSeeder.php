<?php

namespace Database\Seeders;

use App\Models\Masjid;
use App\Services\CmsLandingService;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        /** @var CmsLandingService $service */
        $service = app(CmsLandingService::class);

        $service->saveLandingContent(null, [
            'hero_title' => 'Platform Kewangan Masjid Berbilang Cawangan',
            'hero_subtitle' => 'Pantau kutipan, belanja, dan audit setiap masjid kariah di Malaysia dengan kawalan berpusat.',
            'hero_cta_text' => 'Daftar Masjid Anda',
            'hero_image' => null,
            'features_items' => implode("\n", [
                'Papan pemuka masa nyata untuk hasil dan belanja.',
                'Kawalan akses berperanan untuk pentadbir dan bendahari.',
                'Jejak audit lengkap bagi setiap transaksi kewangan.',
            ]),
            'footer_text' => 'Sistem Kewangan Masjid Malaysia. Semua hak terpelihara.',
            'is_active' => true,
        ]);

        $masjidAlFalah = Masjid::query()->where('code', 'alfalah')->first();

        if ($masjidAlFalah) {
            $service->saveLandingContent($masjidAlFalah->id, [
                'hero_title' => 'Masjid Al-Falah Komited Dengan Ketelusan Kewangan',
                'hero_subtitle' => 'Lihat ringkasan kutipan Jumaat, aktiviti kariah, dan penggunaan tabung khas secara berkala.',
                'hero_cta_text' => 'Lihat Aktiviti Al-Falah',
                'hero_image' => null,
                'features_items' => implode("\n", [
                    'Laporan kutipan mingguan yang mudah difahami jemaah.',
                    'Perancangan belanja program menggunakan baucar digital.',
                    'Pemantauan tabung pembangunan dan kebajikan secara terpisah.',
                ]),
                'footer_text' => 'Portal rasmi Masjid Al-Falah.',
                'is_active' => true,
            ]);
        }
    }
}
