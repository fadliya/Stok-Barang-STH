<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240', 'dimensions:max_width=8192,max_height=8192'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, garis bawah, atau tanda hubung.',
            'photo.image' => 'Unggah file foto yang valid.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 10 MB.',
            'photo.dimensions' => 'Dimensi foto maksimal 8192 × 8192 piksel.',
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            $account = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($account->email !== $data['email']) {
                DB::table('password_reset_tokens')->where('email', $account->email)->delete();
                $account->email_verified_at = null;
            }
            $account->fill(collect($data)->only(['name', 'username', 'email'])->all())->save();
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $bytes = file_get_contents($photo->getRealPath());
                $mime = $photo->getMimeType();
                $dimensions = getimagesize($photo->getRealPath());
                if ($photo->getSize() > 512 * 1024 || max($dimensions[0], $dimensions[1]) > 512) {
                    $image = imagecreatefromstring($bytes);
                    if ($image === false) {
                        throw ValidationException::withMessages(['photo' => 'Foto tidak dapat dibaca. Coba foto lain.']);
                    }
                    $scale = min(1, 512 / max(imagesx($image), imagesy($image)));
                    $thumbnail = imagecreatetruecolor(max(1, (int) (imagesx($image) * $scale)), max(1, (int) (imagesy($image) * $scale)));
                    imagefill($thumbnail, 0, 0, imagecolorallocate($thumbnail, 255, 255, 255));
                    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, imagesx($thumbnail), imagesy($thumbnail), imagesx($image), imagesy($image));
                    ob_start();
                    imagejpeg($thumbnail, null, 85);
                    $bytes = ob_get_clean();
                    $mime = 'image/jpeg';
                    imagedestroy($image);
                    imagedestroy($thumbnail);
                }
                DB::table('user_profile_photos')->updateOrInsert(['user_id' => $user->id], [
                    'data' => base64_encode($bytes),
                    'mime' => $mime, 'created_at' => now(), 'updated_at' => now(),
                ]);
            } elseif ($request->boolean('remove_photo')) {
                DB::table('user_profile_photos')->where('user_id', $user->id)->delete();
            }
        });

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);
        DB::transaction(function () use ($request, $data) {
            $request->user()->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
            DB::table('password_reset_tokens')->where('email', $request->user()->email)->delete();
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
            }
        });
        $request->session()->regenerate();

        return redirect()->route('profile.edit')->with('success', 'Password berhasil diganti. Gunakan password baru untuk login berikutnya.');
    }

    public function photo(Request $request)
    {
        return $this->renderPhoto($request->user());
    }

    public function userPhoto(User $user)
    {
        return $this->renderPhoto($user);
    }

    private function renderPhoto(User $user)
    {
        $photo = DB::table('user_profile_photos')->where('user_id', $user->id)->first();
        if ($photo) {
            return response(base64_decode($photo->data))->header('Content-Type', $photo->mime)
                ->header('Cache-Control', 'private, no-store')->header('X-Content-Type-Options', 'nosniff');
        }

        $initials = htmlspecialchars(mb_strtoupper(mb_substr($user->name, 0, 2)), ENT_XML1, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"><rect width="96" height="96" rx="48" fill="#c4510b"/><text x="48" y="50" text-anchor="middle" dominant-baseline="middle" fill="white" font-family="sans-serif" font-size="30">'.$initials.'</text></svg>';

        return response($svg)->header('Content-Type', 'image/svg+xml')->header('Cache-Control', 'private, no-store')->header('X-Content-Type-Options', 'nosniff');
    }
}
