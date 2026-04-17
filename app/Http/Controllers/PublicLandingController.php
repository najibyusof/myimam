<?php

namespace App\Http\Controllers;

use App\Services\CmsPageBuilderService;
use App\Services\CmsRenderer;
use App\Services\CmsLandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PublicLandingController extends Controller
{
    public function __invoke(
        Request $request,
        CmsPageBuilderService $builderService,
        CmsRenderer $renderer,
        CmsLandingService $cmsService
    ): View {
        $masjid = $request->attributes->get('current_masjid');
        $mode   = setting('landing_page_mode', 'cms');

        if ($mode === 'cms') {
            // 1. Try tenant-specific CMS page first
            if ($masjid) {
                $tenantPage = $builderService->getRenderablePage('home', $masjid->id);
                if ($tenantPage) {
                    $html = $this->safeRender($renderer, $tenantPage->content_json, ['tenantMasjid' => $masjid]);
                    if ($html !== null) {
                        return view('cms.page', [
                            'pageTitle'          => $tenantPage->title,
                            'seoTitle'           => $tenantPage->seo_title ?: $tenantPage->title,
                            'seoDescription'     => $tenantPage->seo_meta_description,
                            'renderedHtml'       => $html,
                            'tenantMasjid'       => $masjid,
                            'tenantSource'       => $request->attributes->get('current_masjid_source'),
                            'bodyClass'          => 'bg-slate-50 text-slate-900 antialiased',
                        ]);
                    }
                }
            }

            // 2. Try global CMS page (no masjid scope)
            $globalPage = $builderService->getRenderablePage('home', null);
            if ($globalPage) {
                $html = $this->safeRender($renderer, $globalPage->content_json, ['tenantMasjid' => $masjid]);
                if ($html !== null) {
                    return view('cms.page', [
                        'pageTitle'          => $globalPage->title,
                        'seoTitle'           => $globalPage->seo_title ?: $globalPage->title,
                        'seoDescription'     => $globalPage->seo_meta_description,
                        'renderedHtml'       => $html,
                        'tenantMasjid'       => $masjid,
                        'tenantSource'       => $request->attributes->get('current_masjid_source'),
                        'bodyClass'          => 'bg-slate-50 text-slate-900 antialiased',
                    ]);
                }
            }
        }

        // 3. Fallback — static landing page
        $payload = $cmsService->getLandingRenderPayload($masjid?->id);

        return view('welcome', [
            'landing'      => $payload,
            'tenantMasjid' => $masjid,
            'tenantSource' => $request->attributes->get('current_masjid_source'),
        ]);
    }

    /**
     * Attempt to render CMS JSON, returning null if the JSON is invalid or rendering fails.
     */
    private function safeRender(CmsRenderer $renderer, mixed $contentJson, array $context = []): ?string
    {
        try {
            if (empty($contentJson)) {
                return null;
            }

            return $renderer->render($contentJson, $context);
        } catch (Throwable) {
            return null;
        }
    }
}
