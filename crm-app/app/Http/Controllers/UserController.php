<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::all();

        return Inertia::render('Users/Index', [
            'users' => $users->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'position' => $u->position,
                'is_active' => $u->is_active,
                'avatar_url' => $u->avatar_url,
                'roles' => $u->roles->pluck('name'),
                'is_microsoft_connected' => $u->isMicrosoftConnected(),
                'created_at' => $u->created_at,
            ]),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        if (isset($data['role']) && Auth::user()->hasRole('admin')) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);
        return back()->with('success', 'Utente aggiornato!');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:8',
        ]);

        Auth::user()->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Password aggiornata!');
    }

    public function profile()
    {
        return Inertia::render('Profile', [
            'user' => Auth::user()->load('roles'),
        ]);
    }
}
