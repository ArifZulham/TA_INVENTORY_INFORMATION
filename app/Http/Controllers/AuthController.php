<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Controller autentikasi — mendukung session web & token Sanctum API.
 */
class AuthController extends Controller
{
    /** Form login (web) */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /** Form register (web) */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /** Proses login session (web) */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('search'));
        }

        return back()
            ->withErrors(['email' => 'Email atau password tidak valid.'])
            ->onlyInput('email');
    }

    /** Proses register session (web) */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($validated);
        Auth::login($user);

        return redirect()->route('search');
    }

    /** Logout session (web) */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** API login — mengembalikan Sanctum token */
    public function apiLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 401);
        }

        $user = $request->user();
        $token = $user->createToken('smartsport-api')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ]);
    }

    /** API register — mengembalikan Sanctum token */
    public function apiRegister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($validated);
        $token = $user->createToken('smartsport-api')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user->only(['id', 'name', 'email']),
            'token' => $token,
        ], 201);
    }

    /** API logout — revoke token aktif */
    public function apiLogout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /** Data user terautentikasi */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email']),
        ]);
    }
}
