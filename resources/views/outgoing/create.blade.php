@extends('layouts.app')

@section('title', 'Form Input Barang Keluar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="manualOutgoingForm()">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Form Input Barang Keluar</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Keluar stok barang secara manual via pilihan form daftar barang.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('pos.scan', ['type' => 'outgoing']) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-bold transition flex items-center space-x-1.5 shadow-md">
                <i class="fa-solid fa-barcode"></i>
                <span>Buka POS Barcode Scanner</span>
            </a>
            <a href="{{ route('outgoing.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-2xl text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        
        <form action="{{ route('outgoing.store') }}" method="POST">
            @csrf

            <!-- Section 1: Header Information -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pb-6 border-b border-slate-100 dark:border-slate-800">
                <!-- Tujuan / Divisi -->
                <div>
                    <label for="tujuan" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Tujuan / Divisi Penerima *</label>
                    <input type="text" name="tujuan" id="tujuan" value="{{ old('tujuan') }}" required
                           placeholder="Contoh: Divisi HRD / Cabang Surabaya"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                </div>

                <!-- Tanggal & Waktu -->
                <div>
                    <label for="tanggal" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Tanggal & Waktu Pengeluaran *</label>
                    <input type="datetime-local" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d\TH:i')) }}" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Catatan / Keterangan</label>
                    <input type="text" name="keterangan" id="keterangan" value="{{ old('keterangan') }}"
                           placeholder="Contoh: Pengeluaran unit kantor / Bon #102"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <!-- Section 2: Items Table -->
            <div class="pt-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Daftar Barang Pengeluaran</h3>
                    <button type="button" @click="addRow()" class="px-3.5 py-2 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 rounded-xl text-xs font-bold transition flex items-center space-x-1">
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
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->kode_barang }} - {{ $item->nama_barang }} (Stok sisa: {{ $item->stok }} {{ $item->satuan }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Qty -->
                            <div class="w-full sm:w-32">
                                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1 sm:hidden">Jumlah</label>
                                <input type="number" :name="`items[${index}][jumlah]`" x-model.number="row.jumlah" min="1" max="2147483647" step="1" required placeholder="Jumlah"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
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
                <button type="submit" class="px-8 py-3.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-rose-600/30 transition flex items-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>SIMPAN TRANSAKSI BARANG KELUAR</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function manualOutgoingForm() {
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
