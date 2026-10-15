@extends('layouts.app')

@section('title', 'Kategori Barang')

@section('content')
<div class="space-y-6" x-data="{ search: '' }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Kategori Barang</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola pengelompokan jenis dan kategori barang inventaris.</p>
        </div>
        <div>
            <a href="{{ route('categories.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-cyan-600/30 transition">
                <i class="fa-solid fa-plus mr-2"></i>
                <span>+ Tambah Kategori Baru</span>
            </a>
        </div>
    </div>

    <!-- Search Toolbar -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" x-model="search" placeholder="Cari nama kategori..." 
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-cyan-500">
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Nama Kategori</th>
                        <th class="px-6 py-4">Jumlah Barang Terkait</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($categories as $index => $category)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition"
                        x-show="search === '' || @js(strtolower($category->nama)).includes(search.toLowerCase())">
                        <td class="px-6 py-4 font-semibold text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold border border-cyan-500/20">
                                <i class="fa-solid fa-tag text-xs"></i>
                            </div>
                            <span>{{ $category->nama }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $category->items_count ?? $category->items->count() }} Item
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('categories.edit', $category->id) }}" class="p-2 text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Edit Kategori">
                                    <i class="fa-solid fa-pen-to-square text-base"></i>
                                </a>
                                <form id="delete-category-{{ $category->id }}" action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(@js('delete-category-'.$category->id), @js('Hapus data: '.$category->nama. '?'))"
                                            class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Hapus Kategori">
                                        <i class="fa-solid fa-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-tags text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p>Belum ada kategori barang terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
