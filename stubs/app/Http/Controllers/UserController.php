<?php

namespace App\Http\Controllers;

use App\Enums\RoleName;
use App\Http\Controllers\Traits\RedirectsToStoredIndex;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use RedirectsToStoredIndex;

    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('roles')
            ->when($request->search, fn ($query, $search) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/Create', [
            'roles' => RoleName::cases(),
        ]);
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $request->persist();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->redirectToIndex('users.index', 'User created successfully.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('users/Edit', [
            'user' => $user->load('roles'),
            'roles' => RoleName::cases(),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $request->persist($user);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->redirectToIndex('users.index', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($user->id === $currentUser->id) {
            return $this->redirectToIndex('users.index', 'You cannot delete yourself.', 'error');
        }

        // Check if current user has permission to delete users
        if (! $currentUser->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            abort(403, 'Unauthorized action.');
        }

        // Only super-admins can delete super-admins
        if ($user->hasRole(RoleName::SuperAdmin->value) && ! $currentUser->hasRole(RoleName::SuperAdmin->value)) {
            return $this->redirectToIndex('users.index', 'You cannot delete a super admin.', 'error');
        }

        $user->delete();

        return $this->redirectToIndex('users.index', 'User deleted successfully.');
    }
}
