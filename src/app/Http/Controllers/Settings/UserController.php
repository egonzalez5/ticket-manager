<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreUserRequest;
use App\Http\Requests\Settings\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $users = User::query()
            ->with('role')
            ->when($request->input('search'), fn($q, $s) =>
                $q->where(fn($q) =>
                    $q->where('name', 'ilike', "%{$s}%")
                      ->orWhere('email', 'ilike', "%{$s}%")
                )
            )
            ->when($request->input('role'), fn($q, $r) =>
                $q->whereHas('role', fn($q) => $q->where('slug', $r))
            )
            ->when($request->filled('status'), fn($q) =>
                $q->where('active', $request->input('status') === 'active')
            )
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn($u) => [
                'id'            => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'role'          => $u->role ? ['id' => $u->role->id, 'name' => $u->role->name, 'slug' => $u->role->slug] : null,
                'active'        => $u->active,
                'last_login_at' => $u->last_login_at?->toISOString(),
                'created_at'    => $u->created_at->toISOString(),
            ]);

        return Inertia::render('Settings/Users', [
            'users'   => $users,
            'roles'   => Role::active()->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => [
                'search' => $request->input('search', ''),
                'role'   => $request->input('role', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'role_id'           => $request->role_id,
            'active'            => $request->boolean('active', true),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('settings.users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role_id' => $request->role_id,
            'active'  => $request->boolean('active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('settings.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggle(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($user->id === $request->user()->id) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['active' => !$user->active]);

        return redirect()->back()
            ->with('success', "User " . ($user->active ? 'activated' : 'deactivated') . " successfully.");
    }
}
