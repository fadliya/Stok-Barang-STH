@extends('layouts.app')

@section('title', 'Laporan Barang Masuk')

@section('content')
<div class="space-y-6" x-data="{ selectedTx: null }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Laporan Barang Masuk</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Rekapitulasi penerimaan barang masuk dari supplier dengan berbagai pilihan filter data.</p>
        </div>
        
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.export-pdf', array_merge(['type' => 'incoming'], request()->all())) }}" target="_blank" rel="noopener noreferrer"
               class="px-4 py-2.5 bg-[#ff8000] hover:bg-[#e67300] text-white rounded-2xl text-xs font-extrabold shadow-md shadow-[#ff8000]/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Export PDF</span>
            </a>
            
            <a href="{{ route('reports.export-excel', array_merge(['type' => 'incoming'], request()->all())) }}"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-extrabold shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export Excel / CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('reports.incoming') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end text-xs">
            <div>
                <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
            </div>
            
            <div>
                <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Tanggal Sampai</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
            </div>

            <div>
                <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Bulan</label>
                <select name="month" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                    <option value="">-- Semua Bulan --</option>
                    @for($m = 1; $m <= 12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Tahun</label>
                <select name="year" class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                    <option value="">-- Semua Tahun --</option>
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2.5 bg-[#ff8000] hover:bg-[#e67300] text-white font-extrabold rounded-xl transition">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('reports.incoming') }}" class="px-3 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 transition" aria-label="Reset Filter" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Kode TRX</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Supplier Pemasok</th>
                        <th class="px-6 py-4 text-center">Jenis Item</th>
                        <th class="px-6 py-4 text-right">Total Masuk</th>
                        <th class="px-6 py-4">Staf Pemroses</th>
                        <th class="px-6 py-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($reports as $index => $row)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-bold text-slate-400">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-mono font-bold text-[#ff8000]">{{ $row->kode_transaksi }}</td>
                        <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-300">{{ $row->tanggal->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $row->supplier->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $row->details->count() }} Jenis Item
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-extrabold text-sm text-emerald-600 dark:text-emerald-400">
                            +{{ number_format($row->total_quantity) }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $row->user->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" @click="selectedTx = @js($row->load(['supplier', 'user', 'details.item']))"
                                    class="p-2 text-slate-400 hover:text-[#ff8000] rounded-xl hover:bg-orange-50 transition" title="Lihat Detail Item">
                                <i class="fa-solid fa-circle-info text-base"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">Tidak ada data transaksi barang masuk sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Detail Breakdown Transaksi -->
    <div x-cloak x-show="selectedTx !== null" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5" @click.outside="selectedTx = null">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#ff8000]/10 text-[#ff8000]" x-text="selectedTx?.kode_transaksi"></span>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-lg mt-1">Rincian Barang Masuk</h3>
                </div>
                <button aria-label="Tutup rincian" type="button" @click="selectedTx = null" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Detail Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase font-extrabold text-[10px]">
                        <tr>
                            <th class="px-4 py-2.5">No</th>
                            <th class="px-4 py-2.5">Barcode / Kode</th>
                            <th class="px-4 py-2.5">Nama Barang</th>
                            <th class="px-4 py-2.5 text-right">Jumlah Masuk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(detail, i) in selectedTx?.details || []" :key="detail.id">
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-400" x-text="i + 1"></td>
                                <td class="px-4 py-3 font-mono font-bold text-[#ff8000]" x-text="detail.item?.barcode || detail.item?.kode_barang"></td>
                                <td class="px-4 py-3 font-extrabold text-slate-900 dark:text-white" x-text="detail.item?.nama_barang"></td>
                                <td class="px-4 py-3 text-right font-extrabold text-emerald-600" x-text="'+' + detail.jumlah + ' ' + (detail.item?.satuan || '')"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="pt-3 text-right">
                <button type="button" @click="selectedTx = null" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-2xl">
                    Tutup
                </button>
            </div>

        </div>
    </div>

</div>
@endsection
