@extends('layouts.app')

@section('title', 'Kelola User / Pengguna')

@section('content')
<div class="space-y-6" x-data="{ search: '', roleFilter: '' }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Kelola User / Pengguna</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola data pengguna sistem, hak akses, dan peranan (Admin, Gudang, Pimpinan).</p>
        </div>
        <div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-cyan-600/30 transition">
                <i class="fa-solid fa-user-plus mr-2"></i>
                <span>+ Tambah User Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" x-model="search" placeholder="Cari nama atau username..." 
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-cyan-500">
        </div>

        <!-- Role Filter Dropdown -->
        <div class="w-full sm:w-48">
            <select x-model="roleFilter" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-cyan-500">
                <option value="">Semua Role</option>
                <option value="admin">Admin</option>
                <option value="gudang">Gudang</option>
                <option value="pimpinan">Pimpinan</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Lengkap</th>
                        <th class="px-6 py-4">Username</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Role / Hak Akses</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($users as $index => $user)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition"
                        x-show="(search === '' || @js(strtolower($user->name)).includes(search.toLowerCase()) || @js(strtolower($user->username)).includes(search.toLowerCase())) && (roleFilter === '' || @js($user->role) === roleFilter)">
                        <td class="px-6 py-4 font-semibold text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-cyan-600 dark:text-cyan-400 border border-slate-200 dark:border-slate-600">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <span>{{ $user->name }}</span>
                        </td>
                        <td class="px-6 py-4 font-mono font-semibold text-cyan-600 dark:text-cyan-400">{{ $user->username }}</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $user->email ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-extrabold tracking-wider rounded-full uppercase
                                {{ $user->isAdmin() ? 'bg-amber-500/20 text-amber-600 dark:text-amber-300 border border-amber-500/30' : '' }}
                                {{ $user->isGudang() ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-500/30' : '' }}
                                {{ $user->isPimpinan() ? 'bg-blue-500/20 text-blue-600 dark:text-blue-300 border border-blue-500/30' : '' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('users.edit', $user->id) }}" class="p-2 text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Edit User">
                                    <i class="fa-solid fa-pen-to-square text-base"></i>
                                </a>

                                @if(auth()->id() !== $user->id)
                                <form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(@js('delete-user-'.$user->id), @js('Hapus data: '.$user->name. '?'))"
                                            class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Hapus User">
                                        <i class="fa-solid fa-trash text-base"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-users-slash text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p>Belum ada pengguna terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
