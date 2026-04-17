<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CmsBuilderUpdateRequest;
use App\Models\Masjid;
use App\Services\CmsPageBuilderService;
use App\Services\CmsRenderer;
use App\Services\LoginDemoAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsBuilderController extends Controller
{
    public function __construct(private readonly CmsPageBuilderService $builderService) {}

    public function edit(Request $request, string $slug = 'home')
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);
        abort_unless($this->builderService->isEditableSlug($slug), 404);

        $requestedTarget = $request->filled('masjid_id')
            ? (int) $request->integer('masjid_id')
            : null;

        $targetMasjidId = $this->builderService->resolveTargetMasjidId($user, $requestedTarget);
        $data = $this->builderService->getBuilderData($slug, $targetMasjidId);

        return view('admin.cms.builder', [
            'slug' => $slug,
            'builderData' => $data,
            'targetMasjidId' => $targetMasjidId,
            'isSuperAdmin' => $user->peranan === 'superadmin',
            'masjids' => $user->peranan === 'superadmin'
                ? Masjid::query()->orderBy('nama')->get(['id', 'nama', 'code'])
                : collect(),
            'editableSlugs' => $this->builderService->editableSlugs(),
        ]);
    }

    public function update(CmsBuilderUpdateRequest $request, string $slug)
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);
        abort_unless($this->builderService->isEditableSlug($slug), 404);

        $requestedTarget = $request->filled('target_masjid_id')
            ? (int) $request->integer('target_masjid_id')
            : null;

        $targetMasjidId = $this->builderService->resolveTargetMasjidId($user, $requestedTarget);
        $layout = json_decode((string) $request->input('content_json'), true);
        $action = (string) $request->input('action', 'save');

        $this->builderService->savePage(
            $user,
            $slug,
            $targetMasjidId,
            (string) $request->string('title'),
            is_array($layout) ? $layout : null,
            $request->boolean('is_active'),
            $action,
            $request->input('seo_title'),
            $request->input('seo_meta_description')
        );

        $message = match ($action) {
            'publish' => 'Halaman CMS berjaya diterbitkan.',
            'unpublish' => 'Halaman CMS berjaya dinyahterbit.',
            default => 'Kandungan CMS berjaya disimpan.',
        };

        return redirect()->route('admin.cms.builder.edit', [
            'slug' => $slug,
            'masjid_id' => $user->peranan === 'superadmin' ? $targetMasjidId : null,
        ])->with('status', $message);
    }

    public function restoreVersion(Request $request, string $slug, int $version): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);
        abort_unless($this->builderService->isEditableSlug($slug), 404);

        $requestedTarget = $request->filled('target_masjid_id')
            ? (int) $request->integer('target_masjid_id')
            : null;

        $targetMasjidId = $this->builderService->resolveTargetMasjidId($user, $requestedTarget);
        $this->builderService->restoreVersion($user, $slug, $targetMasjidId, $version);

        return redirect()->route('admin.cms.builder.edit', [
            'slug' => $slug,
            'masjid_id' => $user->peranan === 'superadmin' ? $targetMasjidId : null,
        ])->with('status', 'Versi halaman berjaya dipulihkan.');
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);

        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'max:4096'],
        ]);

        $path = $validated['image']->store('cms', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
        ]);
    }

    public function mediaLibrary(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);

        $disk = Storage::disk('public');
        $items = collect($disk->files('cms'))
            ->filter(fn(string $path) => preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $path) === 1)
            ->map(fn(string $path) => [
                'name' => basename($path),
                'path' => $path,
                'url' => $disk->url($path),
                'last_modified' => $disk->lastModified($path),
                'size' => $disk->size($path),
            ])
            ->sortByDesc('last_modified')
            ->values()
            ->all();

        return response()->json([
            'items' => $items,
        ]);
    }

    public function preview(Request $request, CmsRenderer $renderer, LoginDemoAccountService $demoAccountService): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);

        $validated = $request->validate([
            'content_json' => ['required', 'array'],
            'content_json.components' => ['nullable', 'array'],
        ]);

        $targetMasjidId = $request->filled('target_masjid_id')
            ? (int) $request->integer('target_masjid_id')
            : null;

        $resolvedMasjidId = $this->builderService->resolveTargetMasjidId($user, $targetMasjidId);
        $tenantMasjid = $resolvedMasjidId ? Masjid::query()->find($resolvedMasjidId) : null;
        $demoData = $demoAccountService->forLoginPage($tenantMasjid);

        $html = $renderer->render($validated['content_json'], [
            'tenantMasjid' => $tenantMasjid,
            'demoAccounts' => $demoData['accounts'],
            'demoCopyPayload' => $demoData['copy_payload'],
        ]);

        return response()->json([
            'html' => $html,
        ]);
    }
}
