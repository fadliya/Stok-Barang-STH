<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (TransportExceptionInterface $exception) {
            // The broker creates the token before sending. Remove it on failure so
            // a failed delivery does not throttle the next attempt for this account.
            Password::deleteToken($user);
            report($exception);

            return back()->withErrors(['email' => 'Layanan email gagal menerima permintaan reset. Silakan coba lagi nanti atau hubungi admin.'])->withInput($request->only('email'));
        }

        if ($status === Password::ResetThrottled) {
            return back()->withErrors(['email' => 'Tautan reset baru saja diminta. Tunggu sebentar sebelum mencoba lagi.'])->withInput($request->only('email'));
        }

        if ($status !== Password::ResetLinkSent) {
            return back()->withErrors(['email' => 'Permintaan reset tidak dapat diproses. Silakan coba lagi.'])->withInput($request->only('email'));
        }

        [$local, $domain] = explode('@', $user->email, 2);
        $destination = mb_substr($local, 0, 1).'***@'.$domain;

        return back()->with('status', 'Permintaan pengiriman tautan reset diterima untuk '.$destination.'. Periksa kotak masuk atau spam. Email masih dapat tertunda atau ditolak oleh layanan email.');
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
