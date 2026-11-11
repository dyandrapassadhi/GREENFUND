<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function form()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'login.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // admin pakai username, donatur pakai email — dua-duanya diterima
        $field = str_contains($data['login'], '@') ? 'email' : 'username';

        if (! Auth::attempt([$field => $data['login'], 'password' => $data['password']], $request->boolean('ingat'))) {
            return back()->withErrors(['login' => 'Email/username atau kata sandi salah.'])->onlyInput('login');
        }

        $request->session()->regenerate();

        // admin langsung ke panel, donatur ke halaman yang tadi dituju
        $target = auth()->user()->isAdmin() ? route('admin.dashboard') : route('katalog');

        return redirect()->intended($target)->with('ok', 'Selamat datang kembali, '.auth()->user()->name.'.');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'donatur',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('katalog')->with('ok', 'Akun berhasil dibuat. Selamat bergabung!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('ok', 'Kamu sudah keluar.');
    }
}
