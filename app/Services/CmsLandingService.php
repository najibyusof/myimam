<?php

namespace App\Services;

use App\Models\CmsContent;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\Masjid;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CmsLandingService
{
    public function resolveTargetMasjidId(User $user, ?int $requestedMasjidId): ?int
    {
        if ($user->peranan === 'superadmin') {
            return $requestedMasjidId;
        }

        return $user->id_masjid ? (int) $user->id_masjid : null;
    }

    public function getLandingEditorData(?int $masjidId): array
    {
        $tenantPage = $this->findPage($masjidId);
        $globalPage = $this->findPage(null);

        return [
            'hero_title' => $this->contentValue($tenantPage, 'hero', 'title')
                ?? $this->contentValue($globalPage, 'hero', 'title')
                ?? 'Sistem Kewangan Masjid Moden',
            'hero_subtitle' => $this->contentValue($tenantPage, 'hero', 'subtitle')
                ?? $this->contentValue($globalPage, 'hero', 'subtitle')
                ?? 'Urus kewangan masjid dengan telus dan efisien.',
            'hero_cta_text' => $this->contentValue($tenantPage, 'hero', 'cta_text')
                ?? $this->contentValue($globalPage, 'hero', 'cta_text')
                ?? 'Mula Sekarang',
            'hero_image' => $this->contentValue($tenantPage, 'hero', 'image')
                ?? $this->contentValue($globalPage, 'hero', 'image')
                ?? null,
            'features_items' => $this->featuresAsTextarea($tenantPage)
                ?? $this->featuresAsTextarea($globalPage)
                ?? "Laporan masa nyata\nKawalan akses berperanan\nAudit trail telus",
            'footer_text' => $this->contentValue($tenantPage, 'footer', 'text')
                ?? $this->contentValue($globalPage, 'footer', 'text')
                ?? 'Hak cipta terpelihara.',
            'is_active' => $tenantPage?->is_active ?? $globalPage?->is_active ?? true,
            'page' => $tenantPage,
            'global_page' => $globalPage,
            'target_masjid' => $masjidId ? Masjid::query()->find($masjidId) : null,
        ];
    }

    public function saveLandingContent(?int $masjidId, array $data): CmsPage
    {
        return DB::transaction(function () use ($masjidId, $data) {
            $page = CmsPage::query()->updateOrCreate(
                [
                    'masjid_id' => $masjidId,
                    'page_name' => 'landing',
                ],
                [
                    'is_active' => $data['is_active'] ?? true,
                ]
            );

            $hero = $this->upsertSection($page, 'hero', 10);
            $features = $this->upsertSection($page, 'features', 20);
            $footer = $this->upsertSection($page, 'footer', 30);

            $this->upsertContentText($hero, 'title', $data['hero_title'] ?? null, 10);
            $this->upsertContentText($hero, 'subtitle', $data['hero_subtitle'] ?? null, 20);
            $this->upsertContentText($hero, 'cta_text', $data['hero_cta_text'] ?? null, 30);
            $this->upsertContentText($hero, 'image', $data['hero_image'] ?? null, 40);

            $featuresItems = collect(preg_split('/\r\n|\r|\n/', (string) ($data['features_items'] ?? '')))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all();

            $this->upsertContentJson($features, 'items', $featuresItems, 10);
            $this->upsertContentText($footer, 'text', $data['footer_text'] ?? null, 10);

            return $page->fresh(['sections.contents']);
        });
    }

    public function getLandingRenderPayload(?int $masjidId): array
    {
        $tenantPage = $masjidId ? $this->findPage($masjidId) : null;
        $globalPage = $this->findPage(null);

        $primaryPage = $tenantPage && $tenantPage->is_active ? $tenantPage : ($globalPage && $globalPage->is_active ? $globalPage : null);
        $fallbackPage = $primaryPage === $tenantPage ? $globalPage : null;

        $heroTitle = $this->contentValue($primaryPage, 'hero', 'title')
            ?? $this->contentValue($fallbackPage, 'hero', 'title')
            ?? 'Sistem Kewangan Masjid Moden';

        $heroSubtitle = $this->contentValue($primaryPage, 'hero', 'subtitle')
            ?? $this->contentValue($fallbackPage, 'hero', 'subtitle')
            ?? 'Urus kewangan masjid dengan telus dan efisien.';

        $heroCta = $this->contentValue($primaryPage, 'hero', 'cta_text')
            ?? $this->contentValue($fallbackPage, 'hero', 'cta_text')
            ?? 'Mula Sekarang';

        $heroImage = $this->contentValue($primaryPage, 'hero', 'image')
            ?? $this->contentValue($fallbackPage, 'hero', 'image');

        $featuresText = $this->featuresAsTextarea($primaryPage)
            ?? $this->featuresAsTextarea($fallbackPage)
            ?? "Laporan masa nyata\nKawalan akses berperanan\nAudit trail telus";

        $footerText = $this->contentValue($primaryPage, 'footer', 'text')
            ?? $this->contentValue($fallbackPage, 'footer', 'text')
            ?? 'Hak cipta terpelihara.';

        $features = collect(preg_split('/\r\n|\r|\n/', (string) $featuresText))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        return [
            'hero_title' => $heroTitle,
            'hero_subtitle' => $heroSubtitle,
            'hero_cta_text' => $heroCta,
            'hero_image' => $heroImage,
            'features' => $features,
            'footer_text' => $footerText,
            'is_active' => (bool) ($primaryPage?->is_active ?? true),
        ];
    }

    private function findPage(?int $masjidId): ?CmsPage
    {
        return CmsPage::query()
            ->where('page_name', 'landing')
            ->where('masjid_id', $masjidId)
            ->with(['sections.contents'])
            ->first();
    }

    private function upsertSection(CmsPage $page, string $section, int $sortOrder): CmsSection
    {
        return CmsSection::query()->updateOrCreate(
            [
                'page_id' => $page->id,
                'section' => $section,
            ],
            [
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]
        );
    }

    private function upsertContentText(CmsSection $section, string $key, ?string $value, int $sortOrder): CmsContent
    {
        return CmsContent::query()->updateOrCreate(
            [
                'section_id' => $section->id,
                'content_key' => $key,
            ],
            [
                'content_text' => $value,
                'content_json' => null,
                'sort_order' => $sortOrder,
            ]
        );
    }

    private function upsertContentJson(CmsSection $section, string $key, array $value, int $sortOrder): CmsContent
    {
        return CmsContent::query()->updateOrCreate(
            [
                'section_id' => $section->id,
                'content_key' => $key,
            ],
            [
                'content_text' => null,
                'content_json' => $value,
                'sort_order' => $sortOrder,
            ]
        );
    }

    private function contentValue(?CmsPage $page, string $section, string $key): ?string
    {
        if (! $page) {
            return null;
        }

        $sectionModel = $page->sections->firstWhere('section', $section);
        if (! $sectionModel) {
            return null;
        }

        $content = $sectionModel->contents->firstWhere('content_key', $key);
        return $content?->content_text;
    }

    private function featuresAsTextarea(?CmsPage $page): ?string
    {
        if (! $page) {
            return null;
        }

        $section = $page->sections->firstWhere('section', 'features');
        if (! $section) {
            return null;
        }

        $content = $section->contents->firstWhere('content_key', 'items');
        if (! $content || ! is_array($content->content_json)) {
            return null;
        }

        return implode("\n", $content->content_json);
    }
}
