<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('panel.dashboard');
        }

        return view('auth.login');
    }

    public function store(Request $request, RateLimiter $limiter): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:4096'],
        ]);

        $key = 'panel-login:'.Str::lower($credentials['email']).'|'.$request->ip();
        if ($limiter->tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please wait '.(string) $limiter->availableIn($key).' seconds and try again.',
            ]);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $limiter->hit($key, 60);
            throw ValidationException::withMessages(['email' => 'The supplied administrator credentials were not recognised.']);
        }

        if (!$request->user()->is_admin) {
            Auth::logout();
            $limiter->hit($key, 60);
            throw ValidationException::withMessages(['email' => 'This account is not an administrator.']);
        }

        $limiter->clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('panel.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('panel.login');
    }
}
