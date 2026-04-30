<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

abstract class BaseFinanceController extends Controller
{
    protected function actor(Request $request): User
    {
        /** @var User $actor */
        $actor = $request->user();

        return $actor;
    }

    protected function perPage(Request $request, int $default = 15): int
    {
        return min(max($request->integer('per_page', $default), 1), 100);
    }

    protected function applyActorMasjidScope(Builder $query, User $actor, string $column = 'id_masjid'): Builder
    {
        if ($actor->peranan === 'superadmin') {
            return $query;
        }

        if (!$actor->id_masjid) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $actor->id_masjid);
    }

    protected function enforceActorScopeForModel(User $actor, Model $model, string $masjidColumn = 'id_masjid'): void
    {
        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && (int) $model->getAttribute($masjidColumn) === (int) $actor->id_masjid,
            403,
            'Unauthorized'
        );
    }

    protected function ensureSuperadminMasjidProvided(User $actor, array $payload): void
    {
        if ($actor->peranan === 'superadmin' && empty($payload['id_masjid'])) {
            throw ValidationException::withMessages([
                'id_masjid' => ['Field id_masjid is required for superadmin requests.'],
            ]);
        }
    }

    protected function paginatedResponse(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    protected function notImplemented(string $operation): JsonResponse
    {
        return response()->json([
            'message' => 'Finance API endpoint scaffolded but not implemented yet.',
            'operation' => $operation,
            'status' => 'planned',
        ], 501);
    }
}
