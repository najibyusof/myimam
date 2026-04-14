<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get all users (paginated)
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        $query = User::query()->visibleTo($actor);
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('aktif')) {
            $query->where('aktif', (bool) $request->input('aktif'));
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->whereHas('roles', function ($roleQuery) use ($role) {
                $roleQuery->where('name', $role);
            });
        }

        $users = $query->with('masjid')
                      ->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    /**
     * Create new user
     */
    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'peranan' => ['nullable', 'string'],
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'roles' => ['array', 'exists:roles,name'],
        ]);

        $targetMasjidId = $actor->peranan === 'superadmin'
            ? ($validated['id_masjid'] ?? null)
            : $actor->id_masjid;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'peranan' => $validated['peranan'] ?? 'User',
            'id_masjid' => $targetMasjidId,
            'aktif' => true,
        ]);

        // Assign roles if provided
        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        } else {
            $user->assignRole('User');
        }

        return response()->json(new UserResource($user), 201);
    }

    /**
     * Get user details
     */
    public function show(User $user): JsonResponse
    {
        $this->ensureUserInScope(request(), $user);

        return response()->json(new UserResource($user->load('masjid')));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->ensureUserInScope($request, $user);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $user->id],
            'peranan' => ['nullable', 'string'],
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->user()->peranan !== 'superadmin') {
            $validated['id_masjid'] = $request->user()->id_masjid;
        }

        $user->update(array_filter($validated));

        return response()->json(new UserResource($user));
    }

    /**
     * Delete user
     */
    public function destroy(User $user): JsonResponse
    {
        $this->ensureUserInScope(request(), $user);

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Toggle user active/inactive status
     */
    public function toggleStatus(User $user): JsonResponse
    {
        $this->ensureUserInScope(request(), $user);

        $user->update([
            'aktif' => !$user->aktif,
        ]);

        return response()->json(new UserResource($user));
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(Request $request, User $user): JsonResponse
    {
        $this->ensureUserInScope($request, $user);

        $validated = $request->validate([
            'roles' => ['required', 'array', 'exists:roles,name'],
        ]);

        $user->syncRoles($validated['roles']);

        return response()->json(new UserResource($user));
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions(User $user): JsonResponse
    {
        $this->ensureUserInScope(request(), $user);

        return response()->json([
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'role_permissions' => $user->getRoleNames()->mapWithKeys(function ($role) {
                $roleObj = \Spatie\Permission\Models\Role::findByName($role);
                return [$role => $roleObj->permissions->pluck('name')];
            }),
        ]);
    }

    private function ensureUserInScope(Request $request, User $subject): void
    {
        $actor = $request->user();

        if ($actor->peranan === 'superadmin') {
            return;
        }

        abort_unless(
            $actor->id_masjid !== null && $subject->id_masjid === $actor->id_masjid,
            403,
            'Unauthorized'
        );
    }
}
