<?php

namespace App\Http\Controllers;

use App\Services\SidebarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SidebarBadgeController extends Controller
{
    /**
     * Return live badge counts for the current authenticated user.
     * Called every 30 seconds by the sidebar Alpine polling loop.
     */
    public function counts(Request $request): JsonResponse
    {
        $service = app(SidebarService::class);

        return response()->json($service->getBadgeCounts());
    }
}
