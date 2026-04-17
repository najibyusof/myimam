<?php

namespace Database\Seeders;

use App\Models\Masjid;
use App\Services\CmsPageBuilderService;
use App\Services\CmsLandingService;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        /** @var CmsPageBuilderService $builderService */
        $builderService = app(CmsPageBuilderService::class);
        $builderService->ensureDefaultComponents();

        $builderService->seedDefaultPage('home', null, 'Laman Utama MyImam', [
            'components' => [
                [
                    'type' => 'hero',
                    'props' => [
                        'title' => 'Platform Operasi Kewangan Masjid Dengan Penyampaian Yang Lebih Profesional',
                        'subtitle' => 'MyImam menyatukan kutipan, belanja, audit, dan pelaporan dalam satu pengalaman digital yang kemas untuk organisasi masjid yang mahu beroperasi dengan lebih yakin dan teratur.',
                        'button_text' => 'Terokai Platform',
                        'button_link' => '/login',
                        'align' => 'center',
                        'padding' => '32px',
                    ],
                ],
                [
                    'type' => 'text',
                    'props' => [
                        'text' => 'Direka untuk pentadbir, bendahari, dan jawatankuasa, MyImam membantu organisasi menyusun proses kerja harian dengan lebih konsisten. Daripada semakan kutipan sehinggalah penyediaan laporan, setiap maklumat penting dipersembahkan secara lebih jelas dan meyakinkan.',
                        'align' => 'left',
                        'margin' => '0 auto 24px',
                    ],
                ],
                [
                    'type' => 'grid',
                    'props' => [
                        'columns' => '3',
                        'items' => "Paparan masa nyata untuk hasil, belanja, dan baki tabung mengikut keutamaan operasi\nAkses berperanan untuk super admin, pentadbir, bendahari, dan pegawai audit tanpa proses yang bercelaru\nJejak transaksi, versi kandungan, dan rekod semakan untuk tadbir urus yang lebih kukuh",
                    ],
                ],
                [
                    'type' => 'image',
                    'props' => [
                        'image_url' => '/cms/defaults/landing-premium.svg',
                        'alt' => 'Ilustrasi premium MyImam untuk operasi kewangan masjid moden',
                    ],
                ],
                [
                    'type' => 'card',
                    'props' => [
                        'title' => 'Operasi Harian Yang Lebih Kemas dan Mudah Dipantau',
                        'text' => 'Pantau kutipan Jumaat, sediakan laporan belanja, urus tabung khas, dan semak bukti transaksi tanpa bergantung pada fail berasingan atau semakan manual yang melambatkan keputusan.',
                        'padding' => '24px',
                    ],
                    'children' => [
                        [
                            'type' => 'button',
                            'props' => [
                                'button_text' => 'Log Masuk Pentadbir',
                                'button_link' => '/login',
                                'align' => 'left',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'card',
                    'props' => [
                        'title' => 'Maklumat Yang Lebih Meyakinkan Untuk Jemaah dan Jawatankuasa',
                        'text' => 'Paparkan ringkasan prestasi kewangan, aktiviti semasa, dan kempen tabung dengan susun atur yang jelas supaya komunikasi rasmi kelihatan lebih tersusun, telus, dan premium.',
                        'padding' => '24px',
                    ],
                ],
                [
                    'type' => 'text',
                    'props' => [
                        'text' => 'Sesuai untuk rangkaian masjid, institusi tunggal, atau pengurusan yang mahu laman rasmi dengan penampilan premium tanpa mengorbankan kemudahan pengurusan melalui CMS Builder.',
                        'align' => 'left',
                    ],
                ],
                [
                    'type' => 'button',
                    'props' => [
                        'button_text' => 'Mula Dengan MyImam',
                        'button_link' => '/login',
                        'align' => 'center',
                    ],
                ],
            ],
        ], true, null, 'MyImam | Platform Operasi Kewangan Masjid Moden', 'MyImam ialah platform CMS dan operasi kewangan masjid yang membantu pengurusan kutipan, belanja, laporan, dan komunikasi komuniti dengan pengalaman moden dan premium.');

        $builderService->seedDefaultPage('login', null, 'Log Masuk MyImam', [
            'components' => [
                [
                    'type' => 'login_form',
                    'props' => [
                        'label'          => 'AKSES DEMO',
                        'left_title'     => 'Sistem Pengurusan Kewangan Masjid',
                        'left_subtitle'  => 'Pengurusan kewangan yang telus, cekap, dan moden.',
                        'feature_1_title' => 'Transparent Operations',
                        'feature_1_text'  => 'Audit-ready records for income, expenses and approvals.',
                        'feature_2_title' => 'Role-based Access',
                        'feature_2_text'  => 'Secure module access for Admin, Bendahari, AJK and Auditor.',
                        'title'          => 'Log Masuk',
                        'subtitle'       => 'Sila log masuk untuk akses sistem kewangan masjid.',
                        'show_demo_accounts' => '1',
                    ],
                ],
            ],
        ], true, null, 'MyImam | Log Masuk Portal', 'Log masuk ke portal operasi MyImam untuk mengurus kutipan, belanja, laporan, dan kandungan rasmi masjid melalui pengalaman digital yang selamat dan profesional.');

        /** @var CmsLandingService $service */
        $service = app(CmsLandingService::class);

        $service->saveLandingContent(null, [
            'hero_title' => 'Platform Operasi Kewangan Masjid Dengan Penyampaian Yang Lebih Profesional',
            'hero_subtitle' => 'Satukan kutipan, belanja, audit, dan pelaporan dalam satu pengalaman digital yang lebih tersusun untuk organisasi masjid yang mahu bergerak dengan yakin.',
            'hero_cta_text' => 'Terokai Platform',
            'hero_image' => '/cms/defaults/landing-premium.svg',
            'features_items' => implode("\n", [
                'Paparan masa nyata untuk hasil, belanja, dan baki tabung yang lebih mudah difahami.',
                'Akses berperanan untuk pentadbir, bendahari, dan pegawai audit tanpa proses yang mengelirukan.',
                'Jejak audit dan pelaporan yang membantu organisasi bergerak dengan tadbir urus yang lebih kukuh.',
            ]),
            'footer_text' => 'MyImam. Platform operasi kewangan masjid yang dibina untuk ketelusan dan keyakinan komuniti.',
            'is_active' => true,
        ]);

        $masjidAlFalah = Masjid::query()->where('code', 'alfalah')->first();

        if ($masjidAlFalah) {
            $builderService->seedDefaultPage('home', $masjidAlFalah->id, 'Laman Utama Masjid Al-Falah', [
                'components' => [
                    [
                        'type' => 'hero',
                        'props' => [
                            'title' => 'Masjid Al-Falah Menampilkan Operasi Kewangan Yang Lebih Tersusun dan Profesional',
                            'subtitle' => 'Ikuti kutipan Jumaat, aktiviti kariah, tabung khas, dan laporan semasa melalui pengalaman digital yang lebih kemas untuk ahli jawatankuasa dan komuniti.',
                            'button_text' => 'Log Masuk Al-Falah',
                            'button_link' => '/login',
                            'align' => 'center',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'props' => [
                            'text' => 'Portal ini memudahkan pihak pengurusan Masjid Al-Falah berkongsi perkembangan kewangan, program utama, dan keperluan komuniti dalam satu halaman yang lebih informatif.',
                            'align' => 'left',
                        ],
                    ],
                    [
                        'type' => 'grid',
                        'props' => [
                            'columns' => '3',
                            'items' => "Laporan kutipan mingguan yang ringkas dan jelas\nPemantauan program dan baucar belanja secara teratur\nRingkasan tabung kebajikan dan pembangunan yang mudah disemak",
                        ],
                    ],
                    [
                        'type' => 'card',
                        'props' => [
                            'title' => 'Tumpuan Utama Komuniti',
                            'text' => 'Semak aktiviti berkala, kempen sumbangan, dan makluman penting tanpa perlu mencari maklumat di pelbagai saluran yang berasingan.',
                            'padding' => '24px',
                        ],
                    ],
                ],
            ]);

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
