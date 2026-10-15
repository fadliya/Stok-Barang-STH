@extends('layouts.app')

@section('title', 'Form Input Barang Masuk')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="manualIncomingForm()">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Form Input Barang Masuk</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tambah stok barang secara manual via pilihan form daftar barang.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('pos.scan', ['type' => 'incoming']) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold transition flex items-center space-x-1.5 shadow-md">
                <i class="fa-solid fa-barcode"></i>
                <span>Buka POS Barcode Scanner</span>
            </a>
            <a href="{{ route('incoming.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-2xl text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        
        <form action="{{ route('incoming.store') }}" method="POST">
            @csrf

            <!-- Section 1: Header Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pb-6 border-b border-slate-100 dark:border-slate-800">
                <!-- Supplier Pemasok -->
                <div>
                    <label for="supplier_id" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Supplier Pemasok *</label>
                    <select name="supplier_id" id="supplier_id" required
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal & Waktu -->
                <div>
                    <label for="tanggal" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Tanggal & Waktu Penerimaan *</label>
                    <input type="datetime-local" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d\TH:i')) }}" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#ff8000]">
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Catatan / Keterangan</label>
                    <input type="text" name="keterangan" id="keterangan" value="{{ old('keterangan') }}"
                           placeholder="Contoh: Pengadaan rutin bulanan / PO #889"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#ff8000]">
                </div>
            </div>

            <!-- Section 2: Items Table -->
            <div class="pt-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Daftar Barang Penerimaan</h3>
                    <button type="button" @click="addRow()" class="px-3.5 py-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 rounded-xl text-xs font-bold transition flex items-center space-x-1">
                        <i class="fa-solid fa-plus"></i>
                        <span>Tambah Baris Barang</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(row, index) in rows" :key="index">
                        <div class="flex flex-col sm:flex-row items-center gap-3 p-4 bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 rounded-2xl">
                            <!-- Barang Selector -->
                            <div class="flex-1 w-full">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1 sm:hidden">Pilih Barang</label>
                                <select :name="`items[${index}][item_id]`" x-model="row.item_id" required
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-[#ff8000]">
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->kode_barang }} - {{ $item->nama_barang }} (Stok saat ini: {{ $item->stok }} {{ $item->satuan }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Qty -->
                            <div class="w-full sm:w-32">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1 sm:hidden">Jumlah</label>
                                <input type="number" :name="`items[${index}][jumlah]`" x-model.number="row.jumlah" min="1" max="2147483647" step="1" required placeholder="Jumlah"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-[#ff8000]">
                            </div>

                            <!-- Remove Button -->
                            <button type="button" @click="removeRow(index)" :disabled="rows.length <= 1"
                                    class="p-2.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition disabled:opacity-30 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-emerald-600/30 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>SIMPAN TRANSAKSI BARANG MASUK</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function manualIncomingForm() {
    return {
        rows: @js(old('items', [['item_id' => '', 'jumlah' => 1]])),
        addRow() {
            this.rows.push({ item_id: '', jumlah: 1 });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        }
    }
}
</script>
@endpush
@endsection
