<?php

namespace App\Http\Controllers;

use App\Services\CmsPageBuilderService;
use App\Services\CmsRenderer;
use App\Services\CmsLandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(
        Request $request,
        CmsPageBuilderService $builderService,
        CmsRenderer $renderer,
        CmsLandingService $cmsService
    ): View {
        $masjid = $request->attributes->get('current_masjid');

        $builderPage = $builderService->getRenderablePage('home', $masjid?->id);
        if ($builderPage) {
            $renderedHtml = $renderer->render($builderPage->content_json, [
                'tenantMasjid' => $masjid,
            ]);

            return view('cms.page', [
                'pageTitle' => $builderPage->title,
                'seoTitle' => $builderPage->seo_title ?: $builderPage->title,
                'seoDescription' => $builderPage->seo_meta_description,
                'renderedHtml' => $renderedHtml,
                'tenantMasjid' => $masjid,
                'tenantSource' => $request->attributes->get('current_masjid_source'),
                'bodyClass' => 'bg-slate-50 text-slate-900 antialiased',
            ]);
        }

        $payload = $cmsService->getLandingRenderPayload($masjid?->id);

        return view('welcome', [
            'landing' => $payload,
            'tenantMasjid' => $masjid,
            'tenantSource' => $request->attributes->get('current_masjid_source'),
        ]);
    }
}
