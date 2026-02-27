<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin() {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenziali non valide.'])->onlyInput('email');
        }

        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Account disattivato. Contatta un amministratore.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function showRegister() {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
        ]);

        // First user gets admin role, others get 'agent'
        if (User::count() === 1) {
            $user->assignRole('admin');
        } else {
            $user->assignRole('agent');
        }

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
