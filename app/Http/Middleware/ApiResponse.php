<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Don't wrap responses that are already JSON formatted
        if (!$response instanceof JsonResponse) {
            return $response;
        }

        return $response;
    }
}
