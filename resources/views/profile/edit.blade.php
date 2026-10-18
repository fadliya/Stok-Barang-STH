@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
<div class="profile-page">
    <div class="profile-heading"><div><span class="profile-eyebrow">Pengaturan Akun</span><h1>Profil Saya</h1><p>Identitas dan keamanan akun, dalam satu tempat.</p></div><span class="profile-heading-icon"><i class="fa-solid fa-user-gear" aria-hidden="true"></i></span></div>
    @if($errors->any())<div role="alert" class="p-4 border border-rose-200 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-200"><p class="font-bold">Periksa kembali data Anda.</p><ul class="list-disc pl-5 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="profile-layout">
        <aside class="profile-summary" aria-label="Ringkasan akun">
            <div class="profile-summary-top"></div>
            <div class="profile-summary-content">
                <img class="profile-avatar" src="{{ route('profile.photo') }}" data-profile-photo="{{ route('profile.photo') }}" alt="Foto profil {{ $user->name }}">
                <h2>{{ $user->name }}</h2><p class="profile-handle">{{ '@'.$user->username }}</p>
                <span class="profile-role"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>{{ ucfirst($user->role) }}</span>
                <dl><div><dt>Email akun</dt><dd>{{ $user->email ?: 'Belum ditambahkan' }}</dd></div><div><dt>Bergabung sejak</dt><dd>{{ $user->created_at?->format('d/m/Y') ?: '-' }}</dd></div></dl>
                <nav class="profile-links" aria-label="Bagian pengaturan profil"><a href="#informasi-akun"><i class="fa-regular fa-user" aria-hidden="true"></i> Informasi akun</a><a href="#keamanan-akun"><i class="fa-solid fa-lock" aria-hidden="true"></i> Keamanan akun</a></nav>
            </div>
        </aside>
        <div class="profile-content">
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="informasi-akun" class="order-panel">
        @csrf @method('PATCH')
        <div class="order-panel-heading"><div class="profile-section-title"><span class="profile-section-icon"><i class="fa-regular fa-address-card" aria-hidden="true"></i></span><div><h2>Informasi Akun</h2><p>Perbarui foto dan informasi pribadi Anda.</p></div></div></div>
        <div class="profile-form-body">
            <div class="profile-photo-editor"><img src="{{ route('profile.photo') }}" data-profile-photo="{{ route('profile.photo') }}" alt="Foto profil {{ $user->name }}" class="w-20 h-20 rounded-full object-cover border border-slate-200 shrink-0"><div class="space-y-2 flex-1 min-w-0"><label for="photo" class="block font-semibold text-sm">Ganti foto profil</label><input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm" aria-describedby="photo-help photo-status"><p id="photo-status" role="status" aria-live="polite" class="text-sm font-semibold text-orange-700 dark:text-orange-300"></p><p id="photo-help" class="text-xs text-slate-500">JPG, PNG, atau WebP. Maksimal 10 MB dan 8192 × 8192 piksel.</p><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_photo" value="1" class="accent-orange-600"> Hapus foto dan gunakan inisial</label></div></div>
            <div class="grid sm:grid-cols-2 gap-5">
                <div><label for="name" class="block text-sm font-semibold mb-2">Nama lengkap</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="w-full p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800"></div>
                <div><label for="username" class="block text-sm font-semibold mb-2">Username</label><input id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="255" autocomplete="username" aria-describedby="username-help" class="w-full p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800"><p id="username-help" class="text-xs text-slate-500 mt-2">Huruf, angka, garis bawah, atau tanda hubung.</p></div>
                <div class="sm:col-span-2"><label for="email" class="block text-sm font-semibold mb-2">Email</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="w-full p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800"><p class="text-xs text-slate-500 mt-2">Gunakan email aktif untuk menerima tautan reset password.</p></div>
            </div>
        </div>
        <div class="profile-footer"><p><i class="fa-regular fa-circle-check mr-1" aria-hidden="true"></i> Perubahan disimpan setelah konfirmasi.</p><button class="order-button order-button-primary" type="submit"><i class="fa-solid fa-check" aria-hidden="true"></i> Simpan Profil</button></div>
    </form>
    <form action="{{ route('profile.password') }}" method="POST" id="keamanan-akun" class="order-panel">
        @csrf @method('PUT')
        <div class="order-panel-heading"><div class="profile-section-title"><span class="profile-section-icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></span><div><h2>Ganti Password</h2><p>Jaga akses akun dengan password yang hanya Anda ketahui.</p></div></div></div>
        <div class="profile-form-body grid sm:grid-cols-2 gap-5">
            <div class="sm:col-span-2"><label for="current_password" class="block text-sm font-semibold mb-2">Password saat ini</label><input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800"></div>
            <div><label for="password" class="block text-sm font-semibold mb-2">Password baru</label><input id="password" name="password" type="password" required minlength="8" maxlength="255" autocomplete="new-password" aria-describedby="password-help" class="w-full p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800"><p id="password-help" class="text-xs text-slate-500 mt-2">Minimal 8 karakter, berbeda dari password lama.</p></div>
            <div><label for="password_confirmation" class="block text-sm font-semibold mb-2">Konfirmasi password baru</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" maxlength="255" autocomplete="new-password" class="w-full p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800"></div>
        </div>
        <div class="profile-footer"><p>Password lama diperlukan untuk melanjutkan.</p><button class="order-button" type="submit"><i class="fa-solid fa-lock" aria-hidden="true"></i> Perbarui Password</button></div>
    </form>
        </div>
    </div>
</div>
@endsection
