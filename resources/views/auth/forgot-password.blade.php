@extends('layouts.guest')

@section('content')
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-2xl space-y-6">
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Lupa Password</h1>
    <p class="text-sm text-slate-600 dark:text-slate-300">Masukkan email yang tersimpan pada akun Anda. Kami akan mengirim tautan untuk membuat password baru. Jika akun belum memiliki email, hubungi admin untuk memperbarui data akun.</p>
    @if(session('status'))
        <div role="status" class="p-4 rounded-2xl bg-orange-50 text-orange-800 text-sm">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-bold mb-2 text-slate-600 dark:text-slate-300">Email akun</label>
            <input id="email" name="email" type="email" autocomplete="email" maxlength="255" value="{{ old('email') }}" required autofocus class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white">
            @error('email')<p role="alert" class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="w-full p-3 bg-[#ff8000] hover:bg-[#e67300] text-white rounded-2xl font-bold">Kirim Tautan Reset</button>
    </form>
    <a href="{{ route('login') }}" class="inline-block text-sm text-[#ff8000] font-bold hover:underline">Kembali ke login</a>
</div>
@endsection
