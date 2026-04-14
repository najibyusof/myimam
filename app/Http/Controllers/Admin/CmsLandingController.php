<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CmsLandingUpdateRequest;
use App\Models\Masjid;
use App\Services\CmsLandingService;
use Illuminate\Http\Request;

class CmsLandingController extends Controller
{
    public function __construct(private CmsLandingService $cmsService)
    {
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ($user->peranan === 'superadmin' || $user->can('cms.manage')), 403);

        $targetMasjidId = $this->cmsService->resolveTargetMasjidId(
            $user,
            $request->filled('masjid_id') ? (int) $request->integer('masjid_id') : null
        );

        $data = $this->cmsService->getLandingEditorData($targetMasjidId);

        return view('admin.cms.landing-edit', [
            'formData' => $data,
            'targetMasjidId' => $targetMasjidId,
            'isSuperAdmin' => $user->peranan === 'superadmin',
            'masjids' => $user->peranan === 'superadmin'
                ? Masjid::query()->orderBy('nama')->get(['id', 'nama', 'code'])
                : collect(),
        ]);
    }

    public function update(CmsLandingUpdateRequest $request)
    {
        $user = $request->user();
        abort_unless($user && ($user->peranan === 'superadmin' || $user->can('cms.manage')), 403);

        $requestedTarget = $request->filled('target_masjid_id')
            ? (int) $request->integer('target_masjid_id')
            : null;

        $targetMasjidId = $this->cmsService->resolveTargetMasjidId($user, $requestedTarget);

        $this->cmsService->saveLandingContent($targetMasjidId, $request->validated());

        return redirect()->route('admin.cms.landing.edit', [
            'masjid_id' => $user->peranan === 'superadmin' ? $targetMasjidId : null,
        ])->with('status', 'Kandungan landing page berjaya disimpan.');
    }
}
