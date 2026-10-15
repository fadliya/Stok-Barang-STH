<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'string', 'max:255']]);
        $identifier = trim($data['email']);
        $column = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($column, $identifier)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email atau username tidak ditemukan.'])->withInput($request->only('email'));
        }

        if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['email' => 'Akun ini belum memiliki email yang valid. Hubungi admin untuk memperbarui email akun.'])->withInput($request->only('email'));
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::ResetThrottled) {
            return back()->withErrors(['email' => 'Tautan reset baru saja diminta. Tunggu sebentar sebelum mencoba lagi.'])->withInput($request->only('email'));
        }

        return back()->with('status', 'Tautan reset password telah dikirim ke email yang terhubung dengan akun. Periksa kotak masuk atau folder spam.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PasswordReset) {
            return redirect()->route('login')->with('info', 'Password berhasil diubah. Silakan masuk menggunakan password baru.');
        }

        return back()->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.'])->withInput($request->only('email'));
    }
}
