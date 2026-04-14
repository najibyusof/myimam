<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    use AuthorizesRequests;

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $actor  = auth()->user();
        $search = trim((string) $request->query('q', ''));

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->visibleTo($actor)
            ->withCount('permissions')
            ->with('masjid:id,nama')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', [
            'roles'       => $roles,
            'search'      => $search,
            'isSuperAdmin' => Role::actorIsSuperAdmin($actor),
        ]);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $actor = auth()->user();

        return view('admin.roles.create', [
            'permissionGroups' => $this->permissionGroups($actor),
            'isSuperAdmin'     => Role::actorIsSuperAdmin($actor),
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $actor       = auth()->user();
        $isSuperAdmin = Role::actorIsSuperAdmin($actor);

        // Determine masjid_id and level based on actor
        $masjidId = $isSuperAdmin
            ? ($request->integer('masjid_id') ?: null)
            : (int) $actor->id_masjid;

        $level = $isSuperAdmin
            ? max(1, min(3, (int) $request->input('level', 3)))
            : 3; // Masjid Admin can only create level-3 roles

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('guard_name', 'web')),
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($q) => $q->where('guard_name', 'web')),
            ],
        ]);

        // Privilege-escalation guard: non-SuperAdmin cannot assign admin+ permissions
        $allowedPermissions = $isSuperAdmin
            ? ($validated['permissions'] ?? [])
            : $this->filterAssignablePermissions($validated['permissions'] ?? []);

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => 'web',
            'masjid_id'  => $masjidId,
            'level'      => $level,
        ]);

        $role->syncPermissions($allowedPermissions);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role created and permissions assigned successfully.');
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(Role $role): View
    {
        abort_unless($role->guard_name === 'web', 404);
        $this->authorize('update', $role);

        $actor = auth()->user();

        return view('admin.roles.edit', [
            'role'               => $role->load('permissions:id,name,guard_name'),
            'permissionGroups'   => $this->permissionGroups($actor),
            'assignedPermissions' => $role->permissions->pluck('name')->all(),
            'isSuperAdmin'       => Role::actorIsSuperAdmin($actor),
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        $this->authorize('update', $role);

        $actor       = auth()->user();
        $isSuperAdmin = Role::actorIsSuperAdmin($actor);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->ignore($role->id)
                    ->where(fn ($q) => $q->where('guard_name', 'web')),
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($q) => $q->where('guard_name', 'web')),
            ],
        ]);

        // Privilege-escalation guard
        $allowedPermissions = $isSuperAdmin
            ? ($validated['permissions'] ?? [])
            : $this->filterAssignablePermissions($validated['permissions'] ?? []);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($allowedPermissions);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role and permissions updated successfully.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);
        $this->authorize('delete', $role);

        // Hard safety: never delete a role that still has users assigned
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', "Cannot delete role \"{$role->name}\" — it is still assigned to {$userCount} user(s).");
        }

        $name = $role->name;
        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('status', "Role \"{$name}\" deleted successfully.");
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * Return grouped permissions.
     * Masjid Admins only see non-privileged permissions (excludes admin+ scoped ones).
     */
    private function permissionGroups($actor): array
    {
        $isSuperAdmin = Role::actorIsSuperAdmin($actor);

        // Permissions Masjid-Admin is NOT allowed to grant
        $restricted = [
            'masjid.create', 'masjid.delete',
            'subscriptions.manage', 'cms.manage', 'settings.manage',
        ];

        return Permission::query()
            ->where('guard_name', 'web')
            ->when(!$isSuperAdmin, fn ($q) => $q->whereNotIn('name', $restricted))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(function (Permission $p) {
                return Str::contains($p->name, '.')
                    ? Str::headline(Str::before($p->name, '.'))
                    : 'General';
            })
            ->map(fn ($perms) => $perms->values())
            ->toArray();
    }

    /**
     * Strip restricted permissions from a non-SuperAdmin's submission
     * to prevent privilege escalation via form tampering.
     */
    private function filterAssignablePermissions(array $permissions): array
    {
        $restricted = [
            'masjid.create', 'masjid.delete',
            'subscriptions.manage', 'cms.manage', 'settings.manage',
        ];

        return array_values(array_filter(
            $permissions,
            fn (string $p) => !in_array($p, $restricted, true)
        ));
    }
}
