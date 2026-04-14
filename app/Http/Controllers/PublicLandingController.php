<?php

namespace App\Http\Controllers;

use App\Services\CmsLandingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(
        Request $request,
        CmsLandingService $cmsService
    ): View {
        $masjid = $request->attributes->get('current_masjid');
        $payload = $cmsService->getLandingRenderPayload($masjid?->id);

        return view('welcome', [
            'landing' => $payload,
            'tenantMasjid' => $masjid,
            'tenantSource' => $request->attributes->get('current_masjid_source'),
        ]);
    }
}
