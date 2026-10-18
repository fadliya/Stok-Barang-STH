@extends('layouts.app')

@section('title', 'Transaksi Barang Masuk')

@section('content')
<div class="space-y-6" x-data="{ selectedTx: null }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Riwayat Barang Masuk</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar transaksi penerimaan pengadaan barang dari supplier. Klik transaksi untuk melihat rincian item.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('goods-requests.index') }}" class="inline-flex items-center px-4 py-2.5 bg-orange-50 text-orange-700 rounded-2xl text-xs font-bold">Permintaan / Pemesanan</a>
            <a href="{{ route('incoming.create') }}" class="inline-flex items-center px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-2xl text-xs font-bold transition">
                <i class="fa-solid fa-[#ff8000] fa-pen-to-square mr-1.5"></i>
                <span>+ Form Manual</span>
            </a>
            <a href="{{ route('pos.scan', ['type' => 'incoming']) }}" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-extrabold shadow-lg shadow-emerald-600/30 transition">
                <i class="fa-solid fa-barcode mr-2 text-sm"></i>
                <span>+ POS Barcode Scanner</span>
            </a>
        </div>
    </div>

    <!-- Search Toolbar -->
    <form method="GET" action="{{ route('incoming.index') }}" class="flex flex-wrap items-center gap-3">
    <div class="bg-white dark:bg-slate-900 p-4 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" aria-label="Cari transaksi" maxlength="255" placeholder="Cari kode TRX, supplier, atau staf..."
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-slate-200 focus:outline-none focus:border-[#ff8000]">
        </div>
    </div>

    <button class="action-primary" type="submit">Cari transaksi</button><a class="action-secondary" href="{{ route('incoming.index') }}">Reset</a>
    </form>
    <section class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-4">
        <h2 class="text-lg font-bold">Pending / Menunggu Barang ({{ $pendingRequests->total() }})</h2>
        <p class="text-sm">Pemesanan yang sudah di-ACC otomatis tampil di sini. Stok baru bertambah setelah pemeriksaan dan konfirmasi penerimaan.</p>
        <div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead><tr><th class="p-3">Nomor pemesanan</th><th class="p-3">Supplier</th><th class="p-3">Tanggal ACC</th><th class="p-3">Jenis barang</th><th class="p-3">Status / tindakan</th></tr></thead><tbody>
        @forelse($pendingRequests as $document)
        <tr class="border-t"><td class="p-3">{{ $document->kode }}</td><td class="p-3">{{ $document->penerima }}</td><td class="p-3">{{ $document->approved_at->format('d/m/Y H:i') }}</td><td class="p-3">{{ $document->details_count }}</td><td class="p-3"><span>Pending</span> · <a class="underline text-orange-600" href="{{ route('goods-requests.show', $document) }}">Periksa barang</a></td></tr>
        @empty<tr><td colspan="5" class="p-3">Tidak ada pesanan pending.</td></tr>@endforelse
        </tbody></table></div>
        {{ $pendingRequests->links() }}
    </section>
    <p class="text-sm text-slate-500" role="status">{{ $transactions->total() }} transaksi penerimaan selesai ditemukan</p>
    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Kode TRX</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Supplier Pemasok</th>
                        <th class="px-6 py-4 text-center">Jumlah Item</th>
                        <th class="px-6 py-4 text-right">Total Qty</th>
                        <th class="px-6 py-4">Staf Pemroses</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($transactions as $index => $tx)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition"
                        >
                        <td class="px-6 py-4 font-bold text-slate-400">{{ $transactions->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-mono font-bold text-[#ff8000]">{{ $tx->kode_transaksi }}</td>
                        <td class="px-6 py-4 font-mono text-slate-600 dark:text-slate-300">{{ $tx->tanggal->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $tx->supplier->nama ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                {{ $tx->details->count() }} Jenis Item
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-extrabold text-sm text-emerald-600 dark:text-emerald-400">
                            +{{ number_format($tx->total_quantity) }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $tx->user->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" @click="selectedTx = @js($tx->load(['supplier', 'user', 'details.item']))"
                                        class="p-2 text-slate-400 hover:text-[#ff8000] rounded-xl hover:bg-orange-50 transition" title="Lihat Rincian Item">
                                    <i class="fa-solid fa-eye text-base"></i>
                                </button>
                                <a href="{{ route('incoming.receipt', $tx->id) }}"
                                   class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-bold text-orange-700 transition-colors hover:border-orange-300 hover:bg-orange-100 active:bg-orange-200 dark:border-orange-800 dark:bg-orange-950/40 dark:text-orange-300 dark:hover:border-orange-600 dark:hover:bg-orange-900/50"
                                   aria-label="Lihat struk {{ $tx->kode_transaksi }}" title="Lihat dan cetak struk">
                                    <i class="fa-solid fa-receipt text-sm" aria-hidden="true"></i>
                                    <span>Struk</span>
                                </a>
                                <form id="delete-incoming-{{ $tx->id }}" action="{{ route('incoming.destroy', $tx->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDelete(@js('delete-incoming-'.$tx->id), @js('Hapus data: '.$tx->kode_transaksi. '?'))"
                                            class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-rose-50 transition" title="Hapus Transaksi">
                                        <i class="fa-solid fa-trash text-base"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-download text-4xl mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p class="font-bold">Belum ada riwayat transaksi barang masuk.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            {!! $transactions->onEachSide(1)->links() !!}
        </div>
        @endif
    </div>

    <!-- Modal Detail Breakdown Transaksi -->
    <div x-cloak x-show="selectedTx !== null" x-trap.inert.noscroll="selectedTx !== null" @keydown.escape.window="selectedTx = null" role="dialog" aria-modal="true" aria-label="Rincian transaksi" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5" @click.outside="selectedTx = null">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#ff8000]/10 text-[#ff8000]" x-text="selectedTx?.kode_transaksi"></span>
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-lg mt-1">Detail Rincian Barang Masuk</h3>
                </div>
                <button aria-label="Tutup rincian" type="button" @click="selectedTx = null" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                <div>
                    <span class="text-slate-400 block">Supplier Pemasok:</span>
                    <span class="font-bold text-slate-900 dark:text-white text-sm" x-text="selectedTx?.supplier?.nama || '-'"></span>
                </div>
                <div>
                    <span class="text-slate-400 block">Tanggal Transaksi:</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="selectedTx?.tanggal"></span>
                </div>
                <div>
                    <span class="text-slate-400 block">Staf Pemroses:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedTx?.user?.name || '-'"></span>
                </div>
                <div>
                    <span class="text-slate-400 block">Catatan:</span>
                    <span class="text-slate-700 dark:text-slate-300" x-text="selectedTx?.keterangan || '-'"></span>
                </div>
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
