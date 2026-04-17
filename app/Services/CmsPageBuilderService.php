<?php

namespace App\Services;

use App\Models\CmsBuilderPage;
use App\Models\CmsComponent;
use App\Models\CmsPageVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CmsPageBuilderService
{
    /**
     * @return array<int, string>
     */
    public function editableSlugs(): array
    {
        return ['home', 'login'];
    }

    public function isEditableSlug(string $slug): bool
    {
        return in_array($slug, $this->editableSlugs(), true);
    }

    public function canManage(User $user): bool
    {
        return $user->peranan === 'superadmin'
            || $user->hasRole('Admin')
            || $user->can('cms.manage')
            || $user->can('manage cms');
    }

    public function resolveTargetMasjidId(User $user, ?int $requestedMasjidId): ?int
    {
        if ($user->peranan === 'superadmin') {
            return $requestedMasjidId;
        }

        return $user->id_masjid ? (int) $user->id_masjid : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBuilderData(string $slug, ?int $masjidId): array
    {
        $scopePage = $this->findBySlug($slug, $masjidId);
        $tenantPage = $scopePage;
        $globalPage = $this->findBySlug($slug, null);

        $effective = $tenantPage ?? $globalPage;
        $layout = $this->normalizeLayout($effective?->content_json, $slug);

        return [
            'title' => $effective?->title ?? ucfirst($slug) . ' Page',
            'seo_title' => $effective?->seo_title ?? ($effective?->title ?? ucfirst($slug) . ' Page'),
            'seo_meta_description' => $effective?->seo_meta_description ?? '',
            'is_active' => $effective?->is_active ?? true,
            'content_json' => $layout,
            'tenant_page' => $tenantPage,
            'global_page' => $globalPage,
            'scope_page_exists' => (bool) $scopePage,
            'versions' => $this->getVersions($slug, $masjidId),
        ];
    }

    public function getRenderablePage(string $slug, ?int $masjidId): ?CmsBuilderPage
    {
        if ($masjidId) {
            $tenantPage = CmsBuilderPage::query()
                ->where('slug', $slug)
                ->where('masjid_id', $masjidId)
                ->where('is_active', true)
                ->first();

            if ($tenantPage) {
                return $tenantPage;
            }
        }

        return CmsBuilderPage::query()
            ->where('slug', $slug)
            ->whereNull('masjid_id')
            ->where('is_active', true)
            ->first();
    }

    /**
     * @param  array<string, mixed>|null  $layout
     */
    public function savePage(
        User $actor,
        string $slug,
        ?int $masjidId,
        string $title,
        ?array $layout,
        bool $isActive,
        string $action = 'save',
        ?string $seoTitle = null,
        ?string $seoMetaDescription = null
    ): CmsBuilderPage {
        $effectiveActiveState = match ($action) {
            'publish' => true,
            'unpublish' => false,
            default => $isActive,
        };

        return DB::transaction(function () use ($actor, $slug, $masjidId, $title, $layout, $effectiveActiveState, $action, $seoTitle, $seoMetaDescription) {
            $page = CmsBuilderPage::query()->updateOrCreate(
                [
                    'masjid_id' => $masjidId,
                    'slug' => $slug,
                ],
                [
                    'title' => $title,
                    'seo_title' => $seoTitle ?: $title,
                    'seo_meta_description' => $seoMetaDescription ?: null,
                    'content_json' => $this->normalizeLayout($layout, $slug),
                    'is_active' => $effectiveActiveState,
                    'created_by' => $actor->id,
                ]
            );

            $this->createVersionSnapshot($page, $actor->id, $action);

            return $page;
        });
    }

    public function restoreVersion(User $actor, string $slug, ?int $masjidId, int $versionId): CmsBuilderPage
    {
        $versionQuery = CmsPageVersion::query()
            ->where('id', $versionId)
            ->where('slug', $slug);

        if ($masjidId === null) {
            $versionQuery->whereNull('masjid_id');
        } else {
            $versionQuery->where('masjid_id', $masjidId);
        }

        $version = $versionQuery->firstOrFail();

        return DB::transaction(function () use ($actor, $slug, $masjidId, $version) {
            $page = CmsBuilderPage::query()->updateOrCreate(
                [
                    'masjid_id' => $masjidId,
                    'slug' => $slug,
                ],
                [
                    'title' => $version->title,
                    'seo_title' => $version->seo_title,
                    'seo_meta_description' => $version->seo_meta_description,
                    'content_json' => $version->content_json,
                    'is_active' => $version->is_active,
                    'created_by' => $actor->id,
                ]
            );

            $this->createVersionSnapshot($page, $actor->id, 'restore');

            return $page;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVersions(string $slug, ?int $masjidId, int $limit = 20): array
    {
        $query = CmsPageVersion::query()
            ->with('creator:id,name')
            ->where('slug', $slug);

        if ($masjidId === null) {
            $query->whereNull('masjid_id');
        } else {
            $query->where('masjid_id', $masjidId);
        }

        return $query
            ->orderByDesc('version_no')
            ->limit($limit)
            ->get()
            ->map(fn(CmsPageVersion $version) => [
                'id' => $version->id,
                'version_no' => $version->version_no,
                'action' => $version->action,
                'title' => $version->title,
                'seo_title' => $version->seo_title,
                'seo_meta_description' => $version->seo_meta_description,
                'is_active' => $version->is_active,
                'content_json' => $version->content_json,
                'created_at' => optional($version->created_at)->toDateTimeString(),
                'created_by' => $version->creator?->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $layout
     */
    public function seedDefaultPage(
        string $slug,
        ?int $masjidId,
        string $title,
        ?array $layout,
        bool $isActive = true,
        ?int $createdBy = null,
        ?string $seoTitle = null,
        ?string $seoMetaDescription = null
    ): CmsBuilderPage {
        return CmsBuilderPage::query()->updateOrCreate(
            [
                'masjid_id' => $masjidId,
                'slug' => $slug,
            ],
            [
                'title' => $title,
                'seo_title' => $seoTitle ?: $title,
                'seo_meta_description' => $seoMetaDescription ?: null,
                'content_json' => $this->normalizeLayout($layout, $slug),
                'is_active' => $isActive,
                'created_by' => $createdBy,
            ]
        );
    }

    public function ensureDefaultComponents(): void
    {
        $components = [
            ['name' => 'Hero Section', 'type' => 'hero', 'schema_json' => ['title' => 'Selamat Datang', 'subtitle' => 'Sistem Kewangan Masjid', 'button_text' => 'Log Masuk', 'button_link' => '/login', 'align' => 'center']],
            ['name' => 'Text Block', 'type' => 'text', 'schema_json' => ['text' => 'Tulis kandungan anda di sini.', 'align' => 'left']],
            ['name' => 'Image', 'type' => 'image', 'schema_json' => ['image_url' => 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?w=1200', 'alt' => 'Imej']],
            ['name' => 'Button', 'type' => 'button', 'schema_json' => ['button_text' => 'Ketahui Lebih Lanjut', 'button_link' => '/login', 'align' => 'left']],
            ['name' => 'Card', 'type' => 'card', 'schema_json' => ['title' => 'Kad Maklumat', 'text' => 'Butiran ringkas untuk pengumuman.', 'padding' => '24px']],
            ['name' => 'Grid', 'type' => 'grid', 'schema_json' => ['columns' => '3', 'items' => "Laporan masa nyata\nKelulusan aliran kerja\nAudit trail"]],
            ['name' => 'Form', 'type' => 'form', 'schema_json' => ['title' => 'Hubungi Kami', 'button_text' => 'Hantar', 'email_to' => 'admin@masjid.com']],
            ['name' => 'Login Form', 'type' => 'login_form', 'schema_json' => ['title' => 'Log Masuk Akaun', 'subtitle' => 'Gunakan akaun anda untuk akses sistem.', 'show_demo_accounts' => '1']],
        ];

        foreach ($components as $component) {
            CmsComponent::query()->updateOrCreate(
                ['type' => $component['type']],
                $component
            );
        }
    }

    private function findBySlug(string $slug, ?int $masjidId): ?CmsBuilderPage
    {
        return CmsBuilderPage::query()
            ->where('slug', $slug)
            ->where('masjid_id', $masjidId)
            ->first();
    }

    private function createVersionSnapshot(CmsBuilderPage $page, ?int $actorId, string $action): void
    {
        $versionScopeQuery = CmsPageVersion::query()->where('slug', $page->slug);
        if ($page->masjid_id === null) {
            $versionScopeQuery->whereNull('masjid_id');
        } else {
            $versionScopeQuery->where('masjid_id', $page->masjid_id);
        }

        $nextVersion = (int) $versionScopeQuery->max('version_no') + 1;

        CmsPageVersion::query()->create([
            'cms_page_id' => $page->id,
            'masjid_id' => $page->masjid_id,
            'slug' => $page->slug,
            'version_no' => $nextVersion,
            'title' => $page->title,
            'seo_title' => $page->seo_title,
            'seo_meta_description' => $page->seo_meta_description,
            'content_json' => $page->content_json,
            'is_active' => $page->is_active,
            'action' => $action,
            'created_by' => $actorId,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $layout
     * @return array<string, mixed>
     */
    private function normalizeLayout(?array $layout, string $slug): array
    {
        if (! $layout || ! isset($layout['components']) || ! is_array($layout['components'])) {
            return $this->defaultLayout($slug);
        }

        return [
            'components' => array_values($layout['components']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultLayout(string $slug): array
    {
        if ($slug === 'login') {
            return [
                'components' => [
                    [
                        'type' => 'login_form',
                        'props' => [
                            'label' => 'AKSES DEMO',
                            'left_title' => 'Sistem Pengurusan Kewangan Masjid',
                            'left_subtitle' => 'Pengurusan kewangan yang telus, cekap, dan moden.',
                            'feature_1_title' => 'Transparent Operations',
                            'feature_1_text' => 'Audit-ready records for income, expenses and approvals.',
                            'feature_2_title' => 'Role-based Access',
                            'feature_2_text' => 'Secure module access for Admin, Bendahari, AJK and Auditor.',
                            'title' => 'Log Masuk',
                            'subtitle' => 'Sila log masuk untuk akses sistem kewangan masjid.',
                            'show_demo_accounts' => '1',
                        ],
                    ],
                ],
            ];
        }

        return [
            'components' => [
                [
                    'type' => 'section',
                    'variant' => 'hero-saas',
                    'props' => [
                        'badge' => 'SaaS Kewangan Masjid',
                        'title' => 'Platform Kewangan Masjid Berbilang Cawangan',
                        'subtitle' => 'Satukan kutipan, belanja, audit, dan pelaporan dalam satu pengalaman digital yang lebih tersusun untuk pengurusan masjid yang moden.',
                        'primary_cta' => [
                            'text' => 'Daftar Masjid Anda',
                            'link' => '/login',
                        ],
                        'secondary_cta' => [
                            'text' => 'Lihat Demo',
                            'link' => '/login',
                        ],
                        'image' => '/cms/defaults/landing-premium.svg',
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'stats-bar',
                    'props' => [
                        'items' => [
                            ['value' => '100+', 'label' => 'Masjid'],
                            ['value' => '50K+', 'label' => 'Transaksi'],
                            ['value' => '10K+', 'label' => 'Laporan'],
                            ['value' => '99.9%', 'label' => 'Ketersediaan'],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'features-grid',
                    'props' => [
                        'title' => 'Ciri-Ciri Utama Untuk Operasi Kewangan Moden',
                        'subtitle' => 'Direka khas untuk pentadbiran masjid pelbagai peranan dan cawangan.',
                        'items' => [
                            [
                                'icon' => 'chart-bar',
                                'title' => 'Dashboard Masa Nyata',
                                'desc' => 'Pantau kutipan, belanja, dan baki secara langsung.',
                            ],
                            [
                                'icon' => 'shield-check',
                                'title' => 'Kawalan Akses Berperanan',
                                'desc' => 'Akses selamat untuk superadmin, admin, bendahari, AJK, dan auditor.',
                            ],
                            [
                                'icon' => 'document-check',
                                'title' => 'Jejak Audit Automatik',
                                'desc' => 'Semua transaksi dan perubahan direkod dengan telus.',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'cta-banner',
                    'props' => [
                        'title' => 'Sedia Untuk Transformasi Digital Kewangan Masjid?',
                        'subtitle' => 'Mulakan hari ini dengan onboarding yang mudah dan sokongan pasukan kami.',
                        'button' => [
                            'text' => 'Daftar Masjid Anda',
                            'link' => '/login',
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'variant' => 'footer-simple',
                    'props' => [
                        'brand' => 'MyImam',
                        'links' => [
                            ['text' => 'Tentang', 'link' => '#'],
                            ['text' => 'Ciri-Ciri', 'link' => '#'],
                            ['text' => 'Hubungi', 'link' => '#'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
