@extends('layouts.guest')

@section('content')
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-2xl">
    
    <!-- Logo & Title -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center p-3 rounded-3xl bg-orange-50 dark:bg-slate-800 border border-[#ff8000]/20 shadow-md mb-4">
            <img src="{{ asset('assets/logo.png') }}" alt="PT STH Network Logo" class="h-16 w-auto object-contain">
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">PT STH NETWORK</h2>
        <p class="text-xs text-[#ff8000] font-extrabold tracking-wider uppercase mt-1">Aplikasi Sistem Stok Barang</p>
    </div>

    @if(session('info'))
        <div class="mb-6 p-4 rounded-2xl bg-orange-50 border border-[#ff8000]/30 text-[#ff8000] text-xs font-bold">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Username Field -->
        <div>
            <label for="username" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Username</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-user"></i>
                </div>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                       placeholder="Masukkan username Anda"
                       class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-[#ff8000] focus:ring-2 focus:ring-[#ff8000]/20 text-sm transition-all">
            </div>
            @error('username')
                <p class="mt-1.5 text-xs text-rose-500 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Password</label>
            <div class="relative" x-data="{ show: false }">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <input :type="show ? 'text' : 'password'" name="password" id="password" required
                       placeholder="Masukkan password Anda"
                       class="w-full pl-10 pr-10 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-[#ff8000] focus:ring-2 focus:ring-[#ff8000]/20 text-sm transition-all">
                <button type="button" aria-label="Tampilkan atau sembunyikan password" :aria-pressed="show" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            @error('password')
                <p class="mt-1.5 text-xs text-rose-500 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center space-x-2 cursor-pointer text-slate-600 dark:text-slate-300 font-bold">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#ff8000] focus:ring-[#ff8000]">
                <span>Ingat Saya</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-[#ff8000] font-bold hover:underline">Lupa password?</a>
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full py-3.5 px-4 bg-[#ff8000] hover:bg-[#e67300] text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-[#ff8000]/30 transition-all duration-200 flex items-center justify-center space-x-2">
            <span>Masuk ke Akun</span>
            <i class="fa-solid fa-arrow-right"></i>
        </button>
    </form>

    <!-- Instagram Link Footer -->
    <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-center">
        <a href="https://www.instagram.com/sthnetwork.id?igsh=MWJvcjQ4ejVxbTNscQ%3D%3D" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center space-x-1.5 text-xs text-[#ff8000] hover:underline font-extrabold transition">
            <i class="fa-brands fa-instagram text-base"></i>
            <span>Instagram: @sthnetwork.id</span>
        </a>
    </div>
</div>
@endsection
