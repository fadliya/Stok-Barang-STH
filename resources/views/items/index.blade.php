@extends('layouts.app')

@section('title', 'Data Barang Inventaris')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Data Barang Inventaris</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar seluruh barang, jumlah stok saat ini, minimum stok, dan supplier.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('items.print-barcodes') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 rounded-2xl text-xs font-bold transition">
                <i class="fa-solid fa-print mr-2"></i>
                <span>Cetak Barcode Massal</span>
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('items.create') }}" class="inline-flex items-center px-5 py-2.5 bg-[#ff8000] hover:bg-[#e67300] text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-[#ff8000]/30 transition">
                <i class="fa-solid fa-plus mr-2"></i>
                <span>+ Tambah Barang Baru</span>
            </a>
            @endif
        </div>
    </div>

<form method="GET" action="{{ route('items.index') }}" class="filter-bar">
    <div class="flex-1 min-w-0"><label for="item-search">Cari inventaris</label><input id="item-search" type="search" name="search" value="{{ request('search') }}" maxlength="255" placeholder="Nama, kode, atau barcode barang"></div>
    <div><label for="item-category">Kategori</label><select id="item-category" name="category"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->nama }}</option>@endforeach</select></div>
    <div><label for="item-stock">Status stok</label><select id="item-stock" name="stock"><option value="">Semua stok</option><option value="low" @selected(request('stock') === 'low')>Perlu pengadaan</option><option value="normal" @selected(request('stock') === 'normal')>Stok aman</option><option value="empty" @selected(request('stock') === 'empty')>Stok habis</option></select></div>
    <button class="action-primary" type="submit">Terapkan</button><a class="action-secondary" href="{{ route('items.index') }}">Reset</a>
</form>
<p class="text-sm text-slate-500 dark:text-slate-400" role="status">{{ $items->total() }} barang ditemukan · Pencarian mencakup seluruh inventaris</p>
    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Nama Barang</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Supplier</th>
                        <th class="px-6 py-4 text-center">Stok Sisa</th>
                        <th class="px-6 py-4 text-center">Min. Stok</th>
                        <th class="px-6 py-4 text-center">Status Stok</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                        <td class="px-6 py-4 font-mono font-bold text-cyan-600 dark:text-cyan-400">{{ $item->kode_barang }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">
                            {{ $item->nama_barang }}
                            @if($item->keterangan)
                            <p class="text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $item->keterangan }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $item->category->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $item->supplier->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-center font-extrabold text-sm">
                            <span class="{{ $item->isLowStock() ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-white' }}">
                                {{ number_format($item->stok) }}
                            </span>
                            <span class="text-[10px] font-normal text-slate-400 ml-1">{{ $item->satuan }}</span>
                        </td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-500">{{ number_format($item->minimum_stok) }} {{ $item->satuan }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($item->isLowStock())
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30 inline-flex items-center">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Hampir Habis
                            </span>
                            @else
                            <span class="px-2.5 py-1 text-[10px] font-extrabold rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 inline-flex items-center">
                                <i class="fa-solid fa-circle-check mr-1"></i> Normal
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('items.show', $item->id) }}" class="p-2 text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Detail Barang">
                                    <i class="fa-solid fa-eye text-base"></i>
                                </a>

                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('items.edit', $item->id) }}" class="p-2 text-slate-400 hover:text-cyan-600 dark:hover:text-cyan-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Edit Barang">
                                    <i class="fa-solid fa-pen-to-square text-base"></i>
                                </a>
                                <form id="delete-item-{{ $item->id }}" action="{{ route('items.destroy', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(@js('delete-item-'.$item->id), @js('Hapus barang '.$item->nama_barang.'?'))"
                                            class="p-2 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition" title="Hapus Barang">
                                        <i class="fa-solid fa-trash text-base"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p>Tidak ada barang yang sesuai. Ubah pencarian atau reset filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            {!! $items->onEachSide(1)->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection
