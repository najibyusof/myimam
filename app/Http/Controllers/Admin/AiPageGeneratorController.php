<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiPageGeneratorService;
use App\Services\CmsPageBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AiPageGeneratorController extends Controller
{
    public function __construct(
        private readonly AiPageGeneratorService $aiService,
        private readonly CmsPageBuilderService $builderService,
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:landing,login,about,donation'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        try {
            $layout = $this->aiService->generateLayout(
                $validated['type'],
                $validated['description']
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $usingFallback = $this->aiService->isFallbackMode();

        return response()->json([
            'success' => true,
            'layout' => $layout,
            'fallback' => $usingFallback,
            'message' => $usingFallback
                ? 'Rekabentuk halaman dijana dari template (tiada kunci API AI dikonfigurasi).'
                : 'Rekabentuk halaman berjaya dijana.',
        ]);
    }

    public function getPresets(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $this->builderService->canManage($user), 403);

        return response()->json([
            'presets' => $this->aiService->getPresetPrompts(),
        ]);
    }
}
