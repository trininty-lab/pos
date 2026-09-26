<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Business;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $actor = $request->user();
        $actorBusinessId = $this->resolveOwnerBusinessId($actor);

        // get all users data
        $users = User::query()
            ->with('roles')
            ->when(! $actor->isSuperAdmin(), function ($query) use ($actorBusinessId, $actor) {
                return $actorBusinessId !== null
                    ? $query->where('business_id', $actorBusinessId)
                    : $query->whereKey($actor->id);
            })
            ->when(request()->search, fn ($query) => $query->where('name', 'like', '%'.request()->search.'%'))
            ->select('id', 'name', 'avatar', 'email')
            ->latest()
            ->paginate(7)
            ->withQueryString();

        // render view
        return Inertia::render('Dashboard/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // get all role data
        $roles = Role::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query->where('scope', '!=', 'system'))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // render view
        return Inertia::render('Dashboard/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $actor = $request->user();
        $actorBusinessId = $this->resolveOwnerBusinessId($actor);

        abort_if(! $actor->isSuperAdmin() && $actorBusinessId === null, 403);
        $this->guardProtectedRoles($request->selectedRoles, $actor);

        $avatarPath = null;

        if ($request->file('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        try {
            DB::transaction(function () use ($request, $avatarPath, $actor, $actorBusinessId): void {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'avatar' => $avatarPath,
                    'business_id' => $actor->isSuperAdmin() ? null : $actorBusinessId,
                ]);

                $user->assignRole($request->selectedRoles);

                $this->auditLogService->log(
                    event: 'user.created',
                    module: 'users',
                    auditable: $user,
                    description: 'Pengguna baru dibuat.',
                    after: $this->userPayload(
                        $user,
                        $this->auditLogService->roleNames($request->selectedRoles),
                        $avatarPath !== null
                    ),
                );
            });
        } catch (\Throwable $exception) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }

            throw $exception;
        }

        // render view
        return to_route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user)
    {
        $this->guardManagedUser($user, $request->user());

        // get all role data
        $roles = Role::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query->where('scope', '!=', 'system'))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // load relationship
        $user->load(['roles' => fn ($query) => $query->select('id', 'name'), 'roles.permissions' => fn ($query) => $query->select('id', 'name')]);

        // render view
        return Inertia::render('Dashboard/Users/Edit', [
            'roles' => $roles,
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $this->guardManagedUser($user, $request->user());
        $this->guardProtectedRoles($request->selectedRoles, $request->user());

        $beforeRoles = $user->roles()->pluck('name')->all();
        $before = $this->userPayload($user, $beforeRoles, false);
        $avatarPath = $user->getRawOriginal('avatar');
        $avatarChanged = false;

        if ($request->file('avatar')) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $avatarChanged = true;
        } elseif ($request->boolean('remove_avatar') && $avatarPath) {
            Storage::disk('public')->delete($avatarPath);
            $avatarPath = null;
            $avatarChanged = true;
        }

        // check if user send request password
        if ($request->password) {
            // update user data password
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }

        // update user data name
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'avatar' => $avatarPath,
        ]);

        // assign role to user
        $user->syncRoles($request->selectedRoles);

        $afterRoles = $this->auditLogService->roleNames($request->selectedRoles);
        $after = $this->userPayload($user->fresh(), $afterRoles, $avatarChanged);

        $this->auditLogService->log(
            event: 'user.updated',
            module: 'users',
            auditable: $user,
            description: 'Data pengguna diperbarui.',
            before: $before,
            after: $after,
        );

        if ($beforeRoles !== $afterRoles) {
            $this->auditLogService->log(
                event: 'user.role_changed',
                module: 'users',
                auditable: $user,
                description: 'Role pengguna diperbarui.',
                before: ['roles' => array_values($beforeRoles)],
                after: ['roles' => array_values($afterRoles)],
            );
        }

        // render view
        return to_route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        abort_if(request()->user()->hasRole('cashier'), 403);

        $ids = explode(',', $id);
        $usersQuery = User::query()->with('roles')->whereIn('id', $ids);
        if (! request()->user()->isSuperAdmin()) {
            $usersQuery->where('business_id', request()->user()->business_id);
        }

        $users = $usersQuery->get();

        foreach ($users as $user) {
            abort_if($user->is(request()->user()), 403);
            $this->guardManagedUser($user, request()->user());
        }

        foreach ($users as $user) {
            $this->auditLogService->log(
                event: 'user.deleted',
                module: 'users',
                auditable: $user,
                description: 'Pengguna dihapus.',
                before: $this->userPayload($user, $user->roles->pluck('name')->all(), false),
            );
        }

        User::whereIn('id', $ids)->delete();

        // render view
        return back();
    }

    private function resolveOwnerBusinessId(User $actor): ?int
    {
        if ($actor->isSuperAdmin()) {
            return null;
        }

        $businessId = $actor->business_id ?? Business::query()->where('owner_id', $actor->id)->value('id');

        if ($businessId !== null && $actor->business_id !== (int) $businessId) {
            $actor->forceFill(['business_id' => $businessId])->saveQuietly();
        }

        return $businessId !== null ? (int) $businessId : null;
    }

    private function userPayload(User $user, array $roles, bool $avatarChanged): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'avatar_changed' => $avatarChanged,
            'roles' => array_values($roles),
        ];
    }

    private function guardProtectedRoles(array|string|null $roles, User $actor): void
    {
        $roleNames = is_array($roles) ? $roles : [$roles];

        if (! $actor->isSuperAdmin() && array_intersect($roleNames, ['super-admin', 'owner'])) {
            abort(403);
        }

        if (! $actor->isSuperAdmin() && Role::whereIn('name', $roleNames)->where('scope', 'system')->exists()) {
            abort(403);
        }
    }

    private function guardManagedUser(User $user, User $actor): void
    {
        if ($actor->isSuperAdmin()) {
            return;
        }

        $actorBusinessId = $this->resolveOwnerBusinessId($actor);

        abort_if($actorBusinessId === null || $user->business_id !== $actorBusinessId, 403);
        abort_if($user->hasAnyRole(['super-admin', 'owner']), 403);
    }
}
