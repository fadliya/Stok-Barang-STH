@extends('layouts.app')

@section('title', 'Universal POS Barcode Scanner')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="universalPosScanner()">
    <div x-cloak x-show="initialReceiptUrl" class="p-4 bg-emerald-50 dark:bg-slate-800 rounded-xl" role="status">Stok awal telah dicatat. <a :href="initialReceiptUrl" target="_blank" rel="noopener noreferrer" class="underline font-bold">Lihat struk penerimaan awal</a></div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="min-w-0">
            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[10px] sm:text-xs font-extrabold border mb-1.5 transition-all max-w-full"
                 :class="transactionType === 'incoming' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 border-rose-500/20'">
                <i class="fa-solid fa-barcode shrink-0"></i>
                <span class="truncate" x-text="transactionType === 'incoming' ? 'POS - BARANG MASUK' : 'POS - BARANG KELUAR'"></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Universal POS Barcode Scanner</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">Scan multi-barang sekaligus untuk transaksi Barang Masuk & Keluar.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('incoming.index') }}" class="flex-1 sm:flex-initial px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 whitespace-nowrap">
                <i class="fa-solid fa-download text-emerald-500"></i>
                <span>Riwayat Masuk</span>
            </a>
            <a href="{{ route('outgoing.index') }}" class="flex-1 sm:flex-initial px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 whitespace-nowrap">
                <i class="fa-solid fa-arrow-up-from-bracket text-rose-500"></i>
                <span>Riwayat Keluar</span>
            </a>
        </div>
    </div>

    <!-- Main POS Card -->
    <div class="bg-white dark:bg-slate-900 p-4 sm:p-8 rounded-2xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-5 sm:space-y-6">

        <!-- Transaction Type Switcher Bar -->
        <div class="p-2 bg-slate-100 dark:bg-slate-800/80 rounded-2xl border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
            <div class="text-[11px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 px-1 flex items-center space-x-2">
                <i class="fa-solid fa-sliders text-[#ff8000]"></i>
                <span>Pilih Transaksi:</span>
            </div>

            <div class="grid grid-cols-2 gap-2 w-full sm:w-auto">
                <button type="button" @click="transactionType = 'incoming'" 
                        :class="transactionType === 'incoming' 
                            ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30 font-black' 
                            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 font-bold'"
                        class="px-3 sm:px-5 py-2.5 rounded-xl text-xs transition flex items-center justify-center space-x-1.5 text-center">
                    <i class="fa-solid fa-circle-down text-xs shrink-0"></i>
                    <span>Masuk (Stok +)</span>
                </button>

                <button type="button" @click="transactionType = 'outgoing'" 
                        :class="transactionType === 'outgoing' 
                            ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30 font-black' 
                            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:bg-slate-50 font-bold'"
                        class="px-3 sm:px-5 py-2.5 rounded-xl text-xs transition flex items-center justify-center space-x-1.5 text-center">
                    <i class="fa-solid fa-circle-up text-xs shrink-0"></i>
                    <span>Keluar (Stok -)</span>
                </button>
            </div>
        </div>

        <form id="universal-pos-form" action="{{ route('pos.store') }}" method="POST" @submit.prevent="submitTransaction()">
            @csrf
            <input type="hidden" name="transaction_type" :value="transactionType">
            <input type="hidden" name="items" :value="JSON.stringify(cartItems)">

            <!-- Section 1: Dynamic Header Information Fields -->
            <div class="grid grid-cols-1 gap-5 pb-6 border-b border-slate-100 dark:border-slate-800"
                 :class="transactionType === 'outgoing' ? 'md:grid-cols-3' : 'md:grid-cols-3'">
                <!-- Tujuan / Divisi (Only for Barang Keluar) -->
                <template x-if="transactionType === 'outgoing'">
                    <div>
                        <label for="tujuan" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Tujuan / Divisi Penerima *</label>
                        <input type="text" name="tujuan" id="tujuan" x-model="tujuan" required
                               placeholder="Contoh: Divisi HRD / Cabang Bandung"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-rose-500">
                    </div>
                </template>

                <!-- Supplier Pemasok (Only for Barang Masuk) -->
                <template x-if="transactionType === 'incoming'">
                    <div>
                        <label for="pos_supplier_id" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Supplier Pemasok *</label>
                        <select name="supplier_id" id="pos_supplier_id" x-model="supplierId" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </template>

                <!-- Tanggal & Waktu -->
                <div>
                    <label for="tanggal" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Tanggal & Waktu Transaksi *</label>
                    <input type="datetime-local" name="tanggal" id="tanggal" x-model="tanggal" required
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#ff8000]">
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Catatan / Keperluan (Opsional)</label>
                    <input type="text" name="keterangan" id="keterangan" x-model="keterangan"
                           placeholder="Contoh: Pengadaan barang / No PO / Penarikan unit"
                           class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-[#ff8000]">
                </div>
            </div>

            <!-- Section 2: Barcode Scanner Input Box & Status Indicator -->
            <div class="py-1 sm:py-2 space-y-2.5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <label for="barcode_input" class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center space-x-1.5">
                        <i class="fa-solid fa-barcode text-base" :class="transactionType === 'incoming' ? 'text-emerald-500' : 'text-rose-500'"></i>
                        <span>Scan / Input Barcode</span>
                    </label>

                    <!-- Status Indicator Pill -->
                    <div class="text-[11px] font-bold px-2.5 py-1 rounded-full w-full sm:w-auto text-center sm:text-left"
                         :class="{
                             'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20': statusState === 'active',
                             'bg-amber-500/10 text-amber-600 border border-amber-500/20': statusState === 'duplicate',
                             'bg-rose-500/10 text-rose-600 border border-rose-500/20': statusState === 'not_found' || statusState === 'stock_error'
                         }">
                        <span x-show="statusState === 'active'"><i class="fa-solid fa-circle text-[8px] mr-1 text-emerald-500 animate-pulse"></i> Scanner Siap</span>
                        <span x-show="statusState === 'duplicate'"><i class="fa-solid fa-circle text-[8px] mr-1 text-amber-500"></i> Duplikat: Qty (+1)</span>
                        <span x-show="statusState === 'not_found'"><i class="fa-solid fa-circle text-[8px] mr-1 text-rose-500"></i> Belum Terdaftar</span>
                        <span x-show="statusState === 'stock_error'"><i class="fa-solid fa-circle text-[8px] mr-1 text-rose-500"></i> Stok Kurang</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 sm:pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-barcode text-lg sm:text-xl"></i>
                        </div>
                        <input id="barcode_input" type="text" x-ref="barcodeInput" x-model="barcodeQuery" @keydown.enter.prevent="handleScan()"
                               placeholder="Arahkan barcode scanner / ketik kode..." 
                               class="w-full pl-10 sm:pl-12 pr-4 sm:pr-48 py-3 sm:py-3.5 bg-slate-50 dark:bg-slate-800 border-2 rounded-2xl text-sm sm:text-base font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:ring-4 transition-all"
                               :class="transactionType === 'incoming' ? 'border-emerald-500/40 focus:border-emerald-500 focus:ring-emerald-500/10' : 'border-rose-500/40 focus:border-rose-500 focus:ring-rose-500/10'">
                        
                        <!-- Desktop action buttons inside input -->
                        <div class="hidden sm:flex absolute right-2 top-2 bottom-2 items-center space-x-1.5">
                            <button type="button" @click="openCameraScanner()" 
                                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center space-x-1">
                                <i class="fa-solid fa-camera"></i>
                                <span>Kamera</span>
                            </button>
                            <button type="button" @click="handleScan()" 
                                    :class="transactionType === 'incoming' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                                    class="px-4 py-2 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center space-x-1">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Scan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile action buttons below input -->
                    <div class="grid grid-cols-2 gap-2 sm:hidden">
                        <button type="button" @click="openCameraScanner()" 
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow transition flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-camera"></i>
                            <span>Scan Kamera</span>
                        </button>
                        <button type="button" @click="handleScan()" 
                                :class="transactionType === 'incoming' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'"
                                class="w-full py-2.5 text-white text-xs font-extrabold rounded-xl shadow transition flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span>Cari / Scan</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Section 3: Scanned Items Cart Table -->
            <div class="pt-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-white flex items-center space-x-2">
                        <i class="fa-solid fa-list-check" :class="transactionType === 'incoming' ? 'text-emerald-500' : 'text-rose-500'"></i>
                        <span>Daftar Hasil Scan Barang</span>
                    </h3>
                    <span class="text-xs font-bold text-slate-500" x-text="cartItems.length + ' Jenis Item (' + totalQty + ' Total Qty)'"></span>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800/60 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-inner">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 uppercase font-extrabold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-5 py-3">No</th>
                                    <th class="px-5 py-3">Barcode / Kode</th>
                                    <th class="px-5 py-3">Nama Barang</th>
                                    <th class="px-5 py-3 text-center">Stok Sisa</th>
                                    <th class="px-5 py-3 text-center">Qty Transaksi</th>
                                    <th class="px-5 py-3">Satuan</th>
                                    <th class="px-5 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-slate-700 dark:text-slate-200">
                                <template x-for="(item, index) in cartItems" :key="item.id">
                                    <tr class="hover:bg-white dark:hover:bg-slate-800 transition" 
                                        :class="(transactionType === 'outgoing' && item.jumlah > item.stok) ? 'bg-rose-50 dark:bg-rose-950/20' : ''">
                                        <td class="px-5 py-3.5 font-bold text-slate-400" x-text="index + 1"></td>
                                        <td class="px-5 py-3.5 font-mono font-bold" :class="transactionType === 'incoming' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'" x-text="item.barcode"></td>
                                        <td class="px-5 py-3.5 font-extrabold text-slate-900 dark:text-white">
                                            <span x-text="item.nama_barang"></span>
                                            <p x-show="transactionType === 'outgoing' && item.jumlah > item.stok" class="text-[10px] text-rose-600 font-bold mt-0.5">⚠️ Qty melebihi stok yang ada!</p>
                                        </td>
                                        <td class="px-5 py-3.5 text-center font-bold text-slate-600 dark:text-slate-300" x-text="item.stok"></td>
                                        <td class="px-5 py-3.5 text-center">
                                            <div class="inline-flex items-center space-x-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl p-1">
                                                <button type="button" @click="decrementQty(index)" class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center">-</button>
                                                <input type="number" x-model.number="item.jumlah" min="1" class="w-12 text-center bg-transparent text-xs font-extrabold focus:outline-none">
                                                <button type="button" @click="incrementQty(index)" class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center">+</button>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 font-semibold text-slate-500" x-text="item.satuan"></td>
                                        <td class="px-5 py-3.5 text-center">
                                            <button type="button" @click="removeItem(index)" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" aria-label="Hapus dari daftar" title="Hapus dari daftar">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                
                                <tr x-show="cartItems.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-barcode text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                                        <p class="font-bold">Belum ada barang di-scan.</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Arahkan barcode scanner atau gunakan kamera ke barcode barang untuk dimasukkan ke daftar.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="$refs.barcodeInput.focus()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-2xl text-xs font-bold transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>+ Scan Lagi / Fokus Scanner</span>
                </button>

                <button type="submit" :disabled="isSubmitDisabled" 
                        :class="isSubmitDisabled 
                            ? 'opacity-50 cursor-not-allowed bg-slate-400 text-white' 
                            : (transactionType === 'incoming' ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-600/30' : 'bg-rose-600 hover:bg-rose-700 text-white shadow-lg shadow-rose-600/30')"
                        class="w-full sm:w-auto px-8 py-3.5 rounded-2xl text-xs font-extrabold transition flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span x-text="transactionType === 'incoming' ? 'SIMPAN TRANSAKSI MASUK (STOK +)' : 'SIMPAN TRANSAKSI KELUAR (STOK -)'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Modal: Barcode Belum Terdaftar (Quick Register Modal) -->
    <div x-cloak x-show="showRegisterModal" x-trap.inert.noscroll="showRegisterModal" @keydown.escape.window="showRegisterModal = false" role="dialog" aria-modal="true" aria-label="Registrasi barang baru"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5" @click.outside="showRegisterModal = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-base">Barang Belum Terdaftar</h3>
                        <p class="text-[11px] text-slate-400">Daftarkan barang baru secara instant ke master data.</p>
                    </div>
                </div>
                <button type="button" @click="showRegisterModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="saveNewItem()">
                <div class="space-y-4 text-xs">
                    <!-- Barcode (Pre-filled) -->
                    <div>
                        <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Barcode (Hasil Scan) *</label>
                        <input type="text" x-model="newItem.barcode" required readonly
                               class="w-full px-3 py-2.5 bg-slate-100 dark:bg-slate-800 font-mono font-bold text-[#ff8000] border border-slate-200 dark:border-slate-700 rounded-xl">
                    </div>

                    <!-- Kode Barang -->
                    <div>
                        <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Kode Barang *</label>
                        <input type="text" x-model="newItem.kode_barang" required
                               class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 font-mono border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                    </div>

                    <!-- Nama Barang -->
                    <div>
                        <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Nama Barang *</label>
                        <input type="text" x-model="newItem.nama_barang" required placeholder="Contoh: Router Mikrotik AX23"
                               class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Kategori -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-bold text-slate-600 dark:text-slate-300">Kategori *</label>
                                <button type="button" @click="isNewCategory = !isNewCategory; if(isNewCategory) newItem.category_id = 'NEW'; else newItem.category_id = ''" 
                                        class="text-[10px] font-bold text-[#ff8000] hover:underline flex items-center space-x-1">
                                    <i class="fa-solid" :class="isNewCategory ? 'fa-list-check' : 'fa-plus'"></i>
                                    <span x-text="isNewCategory ? 'Pilih yang Ada' : '+ Kategori Baru'"></span>
                                </button>
                            </div>
                            <template x-if="!isNewCategory">
                                <select x-model="newItem.category_id" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nama }}</option>
                                    @endforeach
                                </select>
                            </template>
                            <template x-if="isNewCategory">
                                <input type="text" x-model="newItem.new_category_name" required placeholder="Nama Kategori Baru..."
                                       class="w-full px-3 py-2.5 bg-amber-50/50 dark:bg-slate-800 font-bold border-2 border-[#ff8000]/60 rounded-xl focus:border-[#ff8000] focus:outline-none">
                            </template>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-bold text-slate-600 dark:text-slate-300">Supplier *</label>
                                <button type="button" @click="isNewSupplier = !isNewSupplier; if(isNewSupplier) newItem.supplier_id = 'NEW'; else newItem.supplier_id = ''" 
                                        class="text-[10px] font-bold text-[#ff8000] hover:underline flex items-center space-x-1">
                                    <i class="fa-solid" :class="isNewSupplier ? 'fa-list-check' : 'fa-plus'"></i>
                                    <span x-text="isNewSupplier ? 'Pilih yang Ada' : '+ Supplier Baru'"></span>
                                </button>
                            </div>
                            <template x-if="!isNewSupplier">
                                <select x-model="newItem.supplier_id" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->nama }}</option>
                                    @endforeach
                                </select>
                            </template>
                            <template x-if="isNewSupplier">
                                <input type="text" x-model="newItem.new_supplier_name" required placeholder="Nama Supplier Baru..."
                                       class="w-full px-3 py-2.5 bg-amber-50/50 dark:bg-slate-800 font-bold border-2 border-[#ff8000]/60 rounded-xl focus:border-[#ff8000] focus:outline-none">
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Satuan -->
                        <div>
                            <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Satuan *</label>
                            <input type="text" x-model="newItem.satuan" required placeholder="Unit / Pcs / Roll"
                                   class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                        </div>

                        <!-- Stok Awal -->
                        <div>
                            <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1 flex items-center justify-between">
                                <span>Stok Awal *</span>
                                <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-extrabold" x-show="transactionType === 'outgoing'">Auto Inbound</span>
                            </label>
                            <input type="number" x-model.number="newItem.stok" min="0" required
                                   class="w-full px-3 py-2.5 bg-emerald-50/60 dark:bg-slate-800 font-extrabold text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700 rounded-xl focus:border-emerald-500">
                        </div>

                        <!-- Min Stok -->
                        <div>
                            <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Minimum Stok *</label>
                            <input type="number" x-model.number="newItem.minimum_stok" min="0" required
                                   class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                        </div>
                    </div>

                    <template x-if="transactionType === 'outgoing' && newItem.stok > 0">
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center space-x-2 text.xs text-emerald-800 dark:text-emerald-300 font-medium">
                            <i class="fa-solid fa-bolt text-emerald-500 text-sm"></i>
                            <span class="text-[11px]">Sistem akan otomatis mencatat <strong x-text="newItem.stok + ' ' + newItem.satuan"></strong> sebagai <strong>Stok Penerimaan Awal (Auto-Inbound)</strong> agar transaksi barang keluar dapat langsung disimpan.</span>
                        </div>
                    </template>

                    <!-- Keterangan -->
                    <div>
                        <label class="block font-bold text-slate-600 dark:text-slate-300 mb-1">Keterangan (Opsional)</label>
                        <input type="text" x-model="newItem.keterangan" placeholder="Spesifikasi barang..."
                               class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:border-[#ff8000]">
                    </div>
                </div>

                <div class="pt-5 flex justify-end space-x-2">
                    <button type="button" @click="showRegisterModal = false" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl">Batal</button>
                    <button type="submit" :disabled="isRegistering" class="px-5 py-2.5 bg-[#ff8000] hover:bg-[#e67300] text-white text-xs font-extrabold rounded-xl shadow-md">
                        <span x-text="isRegistering ? 'Menyimpan…' : 'Simpan & Masukkan ke Daftar Scan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Camera Barcode Scanner -->
    <div x-cloak x-show="showCameraModal" x-trap.inert.noscroll="showCameraModal" @keydown.escape.window="closeCameraScanner()" role="dialog" aria-modal="true" aria-label="Scan kamera"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
         @click.self="closeCameraScanner()">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-camera text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 dark:text-white text-sm">Scan Barcode via Kamera</h3>
                        <p class="text-[10px] text-slate-400">Arahkan kamera ke barcode / QR Code barang</p>
                    </div>
                </div>
                <button type="button" @click="closeCameraScanner()" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-slate-950 min-h-[260px] flex items-center justify-center border border-slate-800">
                <div id="camera-reader-pos" class="w-full h-full"></div>
                <div x-show="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/90 text-white text-xs space-y-2">
                    <i class="fa-solid fa-circle-notch animate-spin text-2xl text-indigo-500"></i>
                    <span>Mengaktifkan kamera...</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <span class="text-slate-400 text-[11px]"><i class="fa-solid fa-info-circle mr-1"></i> Izinkan akses kamera browser</span>
                <button type="button" @click="closeCameraScanner()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function universalPosScanner() {
    return {
        transactionType: @js(request('type') === 'outgoing' ? 'outgoing' : 'incoming'),
        isSubmitting: false,
        isRegistering: false,
        initialReceiptUrl: null, // incoming, outgoing
        supplierId: '{{ old("supplier_id") }}',
        tujuan: '{{ old("tujuan") }}',
        tanggal: '{{ old("tanggal", date("Y-m-d\TH:i")) }}',
        keterangan: '{{ old("keterangan") }}',
        barcodeQuery: '',
        statusState: 'active', // active, duplicate, not_found, stock_error
        cartItems: [],
        showRegisterModal: false,
        showCameraModal: false,
        cameraLoading: false,
        html5QrScanner: null,
        isNewCategory: false,
        isNewSupplier: false,
        newItem: {
            barcode: '',
            kode_barang: '',
            nama_barang: '',
            category_id: '',
            new_category_name: '',
            supplier_id: '',
            new_supplier_name: '',
            satuan: 'Unit',
            stok: 0,
            minimum_stok: 5,
            keterangan: ''
        },

        init() {
            this.$nextTick(() => {
                if (this.$refs.barcodeInput) {
                    this.$refs.barcodeInput.focus();
                }
            });
        },

        get totalQty() {
            return this.cartItems.reduce((sum, item) => sum + item.jumlah, 0);
        },

        get hasStockExceeded() {
            return this.cartItems.some(item => item.jumlah > item.stok);
        },

        get isSubmitDisabled() {
            if (this.isSubmitting) return true;
            if (this.cartItems.length === 0) return true;
            if (this.transactionType === 'outgoing' && (!this.tujuan || this.hasStockExceeded)) return true;
            return false;
        },

        async handleScan() {
            const query = this.barcodeQuery.trim();
            if (!query) return;

            // Check if item already exists in cart
            const existingIndex = this.cartItems.findIndex(i => i.barcode === query || i.kode_barang === query);
            if (existingIndex !== -1) {
                if (this.transactionType === 'outgoing' && this.cartItems[existingIndex].jumlah + 1 > this.cartItems[existingIndex].stok) {
                    this.statusState = 'stock_error';
                    alert(`Stok barang '${this.cartItems[existingIndex].nama_barang}' tidak mencukupi! Stok tersisa: ${this.cartItems[existingIndex].stok}`);
                    setTimeout(() => this.statusState = 'active', 2500);
                    this.barcodeQuery = '';
                    this.$refs.barcodeInput.focus();
                    return;
                }

                this.cartItems[existingIndex].jumlah += 1;
                this.statusState = 'duplicate';
                this.barcodeQuery = '';
                setTimeout(() => this.statusState = 'active', 2500);
                this.$refs.barcodeInput.focus();
                return;
            }

            // Query API
            try {
                const response = await fetch(`/api/items/scan?barcode=${encodeURIComponent(query)}`);
                const data = await response.json();

                if (data.success && data.item) {
                    if (this.transactionType === 'outgoing' && data.item.stok < 1) {
                        this.statusState = 'stock_error';
                        alert(`Stok barang '${data.item.nama_barang}' saat ini KOSONG (0 ${data.item.satuan})! Tidak dapat dikeluarkan.`);
                        this.barcodeQuery = '';
                        setTimeout(() => this.statusState = 'active', 2500);
                        this.$refs.barcodeInput.focus();
                        return;
                    }

                    if (data.receipt_url) this.initialReceiptUrl = data.receipt_url;
                    this.cartItems.push({
                        id: data.item.id,
                        barcode: data.item.barcode,
                        kode_barang: data.item.kode_barang,
                        nama_barang: data.item.nama_barang,
                        satuan: data.item.satuan,
                        stok: data.item.stok,
                        jumlah: 1
                    });
                    this.statusState = 'active';
                    this.barcodeQuery = '';
                } else {
                    // Item Not Found -> Trigger Modal
                    this.statusState = 'not_found';
                    this.newItem.barcode = query;
                    this.newItem.kode_barang = 'BRG-' + query.slice(-6);
                    this.newItem.supplier_id = this.supplierId || '';
                    this.newItem.stok = 1;
                    this.showRegisterModal = true;
                }
            } catch (err) {
                console.error(err);
                alert('Gagal membaca barcode dari server. Periksa koneksi jaringan.');
            }

            this.$refs.barcodeInput.focus();
        },

        incrementQty(index) {
            if (this.transactionType === 'outgoing' && this.cartItems[index].jumlah + 1 > this.cartItems[index].stok) {
                alert(`Stok tersisa hanya ${this.cartItems[index].stok} ${this.cartItems[index].satuan}!`);
                return;
            }
            this.cartItems[index].jumlah += 1;
        },

        decrementQty(index) {
            if (this.cartItems[index].jumlah > 1) {
                this.cartItems[index].jumlah -= 1;
            }
        },

        removeItem(index) {
            this.cartItems.splice(index, 1);
        },

        openCameraScanner() {
            this.showCameraModal = true;
            this.cameraLoading = true;

            this.$nextTick(async () => {
                try {
                    if (!this.html5QrScanner) {
                        this.html5QrScanner = new (await window.loadBarcodeCamera())("camera-reader-pos");
                    }

                    const config = { 
                        fps: 15, 
                        qrbox: { width: 250, height: 180 },
                        aspectRatio: 1.0
                    };

                    await this.html5QrScanner.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            this.barcodeQuery = decodedText;
                            this.closeCameraScanner();
                            this.handleScan();
                        },
                        (errorMessage) => {
                            // ignore frame mismatch
                        }
                    );
                    this.cameraLoading = false;
                } catch (err) {
                    console.error("Gagal membuka kamera:", err);
                    this.cameraLoading = false;
                    alert("Gagal mengakses kamera. Pastikan browser diberikan izin akses kamera.");
                    this.closeCameraScanner();
                }
            });
        },

        async closeCameraScanner() {
            if (this.html5QrScanner) {
                try {
                    if (this.html5QrScanner.isScanning) {
                        await this.html5QrScanner.stop();
                    }
                } catch (e) {
                    console.warn(e);
                }
            }
            this.showCameraModal = false;
            this.cameraLoading = false;
            this.$nextTick(() => {
                if (this.$refs.barcodeInput) {
                    this.$refs.barcodeInput.focus();
                }
            });
        },

        async saveNewItem() {
            if (this.isRegistering) return;
            this.isRegistering = true;
            const payload = { ...this.newItem };
            if (this.isNewCategory) {
                payload.category_id = 'NEW';
            }
            if (this.isNewSupplier) {
                payload.supplier_id = 'NEW';
            }

            try {
                const response = await fetch('/api/items/quick-store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success && data.item) {
                    if (data.receipt_url) this.initialReceiptUrl = data.receipt_url;
                    this.cartItems.push({
                        id: data.item.id,
                        barcode: data.item.barcode,
                        kode_barang: data.item.kode_barang,
                        nama_barang: data.item.nama_barang,
                        satuan: data.item.satuan,
                        stok: data.item.stok,
                        jumlah: 1
                    });

                    this.showRegisterModal = false;
                    this.statusState = 'active';
                    this.barcodeQuery = '';
                    this.isNewCategory = false;
                    this.isNewSupplier = false;
                    this.newItem.new_category_name = '';
                    this.newItem.new_supplier_name = '';
                    this.$refs.barcodeInput.focus();
                } else {
                    alert(data.message || 'Gagal menyimpan barang baru.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menyimpan barang baru.');
            } finally {
                this.isRegistering = false;
            }
        },

        submitTransaction() {
            if (this.cartItems.length === 0) {
                alert('Silakan scan minimal 1 barang terlebih dahulu!');
                return;
            }
            if (this.transactionType === 'outgoing' && !this.tujuan) {
                alert('Silakan isi tujuan / divisi penerima terlebih dahulu!');
                return;
            }
            if (this.transactionType === 'incoming' && !this.supplierId) {
                alert('Silakan pilih supplier pemasok terlebih dahulu!');
                return;
            }
            if (this.transactionType === 'outgoing' && this.hasStockExceeded) {
                alert('Terdapat barang dengan Qty melebihi stok yang ada! Perbaiki sebelum menyimpan.');
                return;
            }
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            document.getElementById('universal-pos-form').submit();
        }
    }
}
</script>
@endpush
@endsection
