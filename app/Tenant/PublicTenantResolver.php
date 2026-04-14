<?php

namespace App\Tenant;

use App\Models\Masjid;
use Illuminate\Http\Request;

class PublicTenantResolver
{
    public function resolveFromRequest(Request $request): ?Masjid
    {
        return $this->resolveWithSource($request)['masjid'];
    }

    public function resolveWithSource(Request $request): array
    {
        $fromQuery = $this->resolveFromQuery($request);
        if ($fromQuery) {
            return ['masjid' => $fromQuery, 'source' => 'query'];
        }

        $fromSubdomain = $this->resolveFromSubdomain($request);
        if ($fromSubdomain) {
            return ['masjid' => $fromSubdomain, 'source' => 'subdomain'];
        }

        $fromSession = $this->resolveFromSession($request);
        if ($fromSession) {
            return ['masjid' => $fromSession, 'source' => 'session'];
        }

        return ['masjid' => null, 'source' => null];
    }

    private function resolveFromQuery(Request $request): ?Masjid
    {
        $identifier = trim((string) $request->query('masjid'));
        if ($identifier === '') {
            return null;
        }

        return Masjid::query()
            ->when(is_numeric($identifier), function ($query) use ($identifier) {
                $query->where('id', (int) $identifier);
            }, function ($query) use ($identifier) {
                $query->where('code', $identifier);
            })
            ->first();
    }

    private function resolveFromSubdomain(Request $request): ?Masjid
    {
        $host = strtolower((string) $request->getHost());
        $baseHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($host === '' || $baseHost === '' || $host === $baseHost) {
            return null;
        }

        $suffix = '.' . $baseHost;
        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen($suffix));
        if ($subdomain === '' || str_contains($subdomain, '.')) {
            return null;
        }

        return Masjid::query()->where('code', $subdomain)->first();
    }

    private function resolveFromSession(Request $request): ?Masjid
    {
        $id = $request->session()->get('tenant.masjid_id');

        if (! $id) {
            return null;
        }

        return Masjid::query()->find((int) $id);
    }
}
