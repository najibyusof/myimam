<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\Masjid;
use App\Models\Role;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $actor = $request->user();
        $masjidScope = $actor->peranan === 'superadmin' ? null : $actor->id_masjid;

        $query = User::query()
            ->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope))
            ->with(['roles:id,name', 'masjid:id,nama'])
            ->latest('id');

        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $role = trim((string) $request->query('role', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('aktif', $status === 'active');
        }

        if ($role !== '') {
            $query->whereHas('roles', function ($roleQuery) use ($role) {
                $roleQuery->where('name', $role);
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->assignableTo($actor)
            ->orderBy('name')
            ->pluck('name');
        $baseStatsQuery = User::query()->when($masjidScope, fn($builder) => $builder->byMasjid($masjidScope));

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'active' => (clone $baseStatsQuery)->active()->count(),
            'inactive' => (clone $baseStatsQuery)->where('aktif', false)->count(),
        ];

        return view('admin.users.index', compact('users', 'search', 'status', 'role', 'roles', 'stats'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->assignableTo($request->user())
            ->orderBy('name')
            ->pluck('name');
        $masjidOptions = $this->masjidOptions($request);

        return view('admin.users.create', compact('roles', 'masjidOptions'));
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User created successfully.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->assignableTo($request->user())
            ->orderBy('name')
            ->pluck('name');
        $masjidOptions = $this->masjidOptions($request);

        return view('admin.users.edit', compact('user', 'roles', 'masjidOptions'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->service->update($request->user(), $user, $request->validated());

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->service->delete($user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        $user->update(['aktif' => !$user->aktif]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', $user->aktif ? 'User activated successfully.' : 'User deactivated successfully.');
    }

    public function sendPasswordReset(User $user): RedirectResponse
    {
        $this->authorize('resetPassword', $user);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()
                ->route('admin.users.edit', $user)
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Password reset link sent successfully.');
    }

    private function masjidOptions(Request $request)
    {
        if ($request->user()->peranan === 'superadmin') {
            return Masjid::query()->orderBy('nama')->get(['id', 'nama']);
        }

        return Masjid::query()->whereKey($request->user()->id_masjid)->get(['id', 'nama']);
    }
}
