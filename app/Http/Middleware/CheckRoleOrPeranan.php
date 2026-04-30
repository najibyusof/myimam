<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPeranan
{
    public function handle(Request $request, Closure $next, string ...$allowed): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Akses tidak dibenarkan.');
        }

        $targets = collect($allowed)
            ->flatMap(fn (string $item) => explode('|', $item))
            ->map(fn (string $item) => strtolower(trim($item)))
            ->filter()
            ->values();

        if ($targets->isEmpty()) {
            return $next($request);
        }

        $peranan = strtolower((string) ($user->peranan ?? ''));
        if ($peranan !== '' && $targets->contains($peranan)) {
            return $next($request);
        }

        if (method_exists($user, 'getRoleNames')) {
            $roleNames = collect($user->getRoleNames())
                ->map(fn (string $role) => strtolower(trim($role)));

            if ($targets->intersect($roleNames)->isNotEmpty()) {
                return $next($request);
            }
        }

        abort(403, 'Akses tidak dibenarkan.');
    }
}
