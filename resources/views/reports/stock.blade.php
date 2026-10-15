@extends('layouts.app')

@section('title', 'Laporan Stok Barang')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Laporan Posisi Stok Barang</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rekap data posisi stok terkini seluruh barang inventaris PT. Suka Tari Group.</p>
        </div>
        
        <!-- Export Action Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.export-pdf', ['type' => 'stock']) }}" target="_blank" rel="noopener noreferrer"
               class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-pdf text-sm"></i>
                <span>Export PDF / Print</span>
            </a>

            <a href="{{ route('reports.export-excel', ['type' => 'stock']) }}"
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center space-x-1.5">
                <i class="fa-solid fa-file-excel text-sm"></i>
                <span>Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Kode Barang</th>
                        <th class="px-6 py-4">Nama Barang</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Supplier</th>
                        <th class="px-6 py-4 text-center">Stok Sisa</th>
                        <th class="px-6 py-4 text-center">Min. Stok</th>
                        <th class="px-6 py-4 text-center">Status Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300">
                    @forelse($items as $index => $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                        <td class="px-6 py-4 font-semibold text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-mono font-bold text-cyan-600 dark:text-cyan-400">{{ $item->kode_barang }}</td>
                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-white">{{ $item->nama_barang }}</td>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p>Belum ada data barang terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
