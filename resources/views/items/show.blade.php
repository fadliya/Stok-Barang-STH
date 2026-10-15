@extends('layouts.app')

@section('title', 'Detail Barang - ' . $item->nama_barang)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20 mb-1 inline-block">
                {{ $item->kode_barang }}
            </span>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $item->nama_barang }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Informasi detail barang, stok tersisa, dan riwayat transaksi.</p>
        </div>
        <a href="{{ route('items.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Top Card Specs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Stok Saat Ini</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-1">{{ number_format($item->stok) }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Satuan: {{ $item->satuan }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-box"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Minimum Stok</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-1">{{ number_format($item->minimum_stok) }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">Batas Alert Peringatan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Stok</p>
                <div class="mt-2">
                    @if($item->isLowStock())
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Hampir Habis
                    </span>
                    @else
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                        <i class="fa-solid fa-circle-check mr-1"></i> Stok Safe
                    </span>
                    @endif
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                <i class="fa-solid fa-shield"></i>
            </div>
        </div>
    </div>

    <!-- Metadata Details -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-700/60 pb-3">Informasi Master Barang</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block mb-1">Kode Barcode:</span>
                <p class="font-mono font-bold text-[#ff8000] text-sm flex items-center space-x-1">
                    <i class="fa-solid fa-barcode mr-1 text-slate-400"></i>
                    <span>{{ $item->barcode ?? '-' }}</span>
                </p>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">Kategori Barang:</span>
                <p class="font-bold text-slate-800 dark:text-white text-sm">{{ $item->category->nama ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 block mb-1">Supplier Pemasok:</span>
                <p class="font-bold text-slate-800 dark:text-white text-sm">{{ $item->supplier->nama ?? '-' }}</p>
            </div>
            <div class="md:col-span-3">
                <span class="text-slate-400 block mb-1">Keterangan / Catatan:</span>
                <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $item->keterangan ?? 'Tidak ada keterangan.' }}</p>
            </div>
        </div>
    </div>

    <!-- History barang masuk & keluar -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Riwayat Barang Masuk -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white mb-3 flex items-center">
                <i class="fa-solid fa-download text-emerald-500 mr-2"></i>
                Riwayat Barang Masuk
            </h3>
            <div class="space-y-3">
                @forelse($item->incomingDetails->take(5) as $inc)
                <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-between text-xs border border-slate-100 dark:border-slate-800">
                    <div>
                        <p class="font-bold text-emerald-600 dark:text-emerald-400">+{{ $inc->jumlah }} {{ $item->satuan }}</p>
                        <p class="text-[10px] text-slate-400">
                            {{ optional($inc->transaction?->tanggal)->format('d M Y, H:i') ?? '-' }} - Staf: {{ $inc->transaction?->user?->name ?? '-' }}
                        </p>
                    </div>
                    <span class="text-[10px] text-slate-400">{{ $inc->transaction?->supplier?->nama ?? '-' }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">Belum ada transaksi barang masuk.</p>
                @endforelse
            </div>
        </div>

        <!-- Riwayat Barang Keluar -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <h3 class="font-bold text-sm text-slate-800 dark:text-white mb-3 flex items-center">
                <i class="fa-solid fa-arrow-up-from-bracket text-rose-500 mr-2"></i>
                Riwayat Barang Keluar
            </h3>
            <div class="space-y-3">
                @forelse($item->outgoingDetails->take(5) as $out)
                <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-between text-xs border border-slate-100 dark:border-slate-800">
                    <div>
                        <p class="font-bold text-rose-600 dark:text-rose-400">-{{ $out->jumlah }} {{ $item->satuan }}</p>
                        <p class="text-[10px] text-slate-400">
                            {{ optional($out->transaction?->tanggal)->format('d M Y, H:i') ?? '-' }} - Staf: {{ $out->transaction?->user?->name ?? '-' }}
                        </p>
                    </div>
                    <span class="text-[10px] text-slate-400">Tujuan: {{ $out->transaction?->tujuan ?? '-' }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">Belum ada transaksi barang keluar.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Large Barcode Card for Scanning & Testing -->
    <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm text-center space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-barcode text-[#ff8000] text-xl"></i>
                <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Label Barcode Barang (Siap Scan)</h3>
            </div>
            <span class="px-3 py-1 bg-[#ff8000]/10 text-[#ff8000] font-mono font-extrabold text-xs rounded-full border border-[#ff8000]/20">
                CODE128
            </span>
        </div>

        <div class="py-6 px-4 bg-white rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center space-y-3 shadow-inner">
            <!-- Visual SVG Barcode Generated via JsBarcode -->
            <svg id="barcode-canvas" class="max-w-full h-auto"></svg>
            <p class="font-mono font-black text-2xl tracking-widest text-slate-900 mt-2">{{ $item->barcode }}</p>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ $item->nama_barang }} ({{ $item->kode_barang }})</p>
        </div>
        <p class="text-[11px] text-slate-400">💡 Anda bisa mengambil foto / mengarahkan kamera HP Anda ke barcode besar di atas untuk menguji fitur <strong>Scan Kamera Web</strong>.</p>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener("app:ready", function() {
        const barcodeValue = @js($item->barcode);
        if (barcodeValue) {
            JsBarcode("#barcode-canvas", barcodeValue, {
                format: "CODE128",
                lineColor: "#0f172a",
                width: 3.5,
                height: 120,
                displayValue: false,
                margin: 20,
                background: "#ffffff"
            });
        }
    });
</script>
@endpush
@endsection
