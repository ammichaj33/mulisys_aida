<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirection selon les permissions
            if ($user->can('view-dashboard-receptionniste')) {
                return redirect()->route('receptionniste.dashboard');
            } elseif ($user->can('view-dashboard-charge-credits')) {
                return redirect()->route('charge_credits.dashboard');
            } elseif ($user->can('view-dashboard-gerant')) {
                return redirect()->route('gerant.dashboard');
            } elseif ($user->can('view-dashboard-caissiere')) {
                return redirect()->route('caissiere.dashboard');
            } elseif ($user->can('view-dashboard-directeur')) {
                return redirect()->route('directeur.dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        }

        return back()->withErrors([
            'username' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('username');
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
