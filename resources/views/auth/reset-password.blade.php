@extends('layouts.guest')

@section('content')
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Buat Password Baru</h1>
    <p class="text-sm text-slate-600 dark:text-slate-300">Gunakan password minimal 8 karakter. Tautan reset berlaku selama {{ config('auth.passwords.users.expire') }} menit dan hanya dapat digunakan sekali.</p>
    @if($errors->any())
        <div role="alert" class="p-4 rounded-2xl bg-rose-50 text-rose-800 text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="block text-sm font-bold mb-2 text-slate-600 dark:text-slate-300">Email akun</label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="255" value="{{ old('email', $email) }}" required class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white">
        </div>
        <div>
            <label for="password" class="block text-sm font-bold mb-2 text-slate-600 dark:text-slate-300">Password baru</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white">
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-bold mb-2 text-slate-600 dark:text-slate-300">Konfirmasi password baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white">
        </div>
        <button type="submit" class="w-full p-3 bg-[#ff8000] hover:bg-[#e67300] text-white rounded-2xl font-bold">Simpan Password Baru</button>
    </form>
    <a href="{{ route('password.request') }}" class="inline-block text-sm text-[#ff8000] font-bold hover:underline">Minta tautan reset baru</a>
</div>
@endsection
