<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        abort_if(blank(config('services.google.client_id')), 404, 'Google login belum dikonfigurasi.');

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        abort_if(blank(config('services.google.client_id')), 404, 'Google login belum dikonfigurasi.');

        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['email' => 'Login Google dibatalkan.']);
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['email' => 'Gagal masuk dengan Google: '.$e->getMessage()]);
        }

        $user = User::where('email', $google->getEmail())->first();

        if ($user) {
            // akun lama (mis. admin) — cukup login, jangan ubah role
            $user->forceFill([
                'google_id' => $user->google_id ?: $google->getId(),
                'avatar' => $google->getAvatar(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $google->getName() ?: Str::before($google->getEmail(), '@'),
                'email' => $google->getEmail(),
                'password' => bcrypt(Str::random(32)),
                'role' => 'donatur',
                'google_id' => $google->getId(),
                'avatar' => $google->getAvatar(),
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('katalog'))
            ->with('ok', 'Berhasil masuk dengan Google. Selamat datang, '.$user->name.'!');
    }
}
