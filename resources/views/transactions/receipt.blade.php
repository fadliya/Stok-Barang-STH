@extends('layouts.app')
@section('title', 'Bukti Barang '.($type === 'incoming' ? 'Masuk' : 'Keluar'))
@section('content')
@php
    $incoming = $type === 'incoming';
    $unitTotals = $transaction->details->groupBy(fn ($detail) => $detail->item->satuan ?? 'unit')->map(fn ($details) => $details->sum('jumlah'));
@endphp
<div class="receipt-page space-y-6">
    <div class="receipt-toolbar flex flex-wrap items-center justify-between gap-4">
        <div><p class="eyebrow">DOKUMEN INVENTARIS</p><h1 class="text-2xl font-bold">Bukti barang {{ $incoming ? 'masuk' : 'keluar' }}</h1><p class="text-sm text-slate-500 dark:text-slate-400">Transaksi tercatat. Struk dapat dicetak kembali dari riwayat.</p></div>
        <a class="action-secondary" href="{{ route($type.'.index') }}">← Riwayat transaksi</a>
    </div>
    <div class="receipt-toolbar receipt-controls">
        <div><label for="receipt-paper" class="text-sm font-semibold">Ukuran kertas</label><select id="receipt-paper" class="ml-2 rounded-lg border border-slate-300 bg-white dark:bg-slate-800 px-3 py-2"><option value="a4">A4 / PDF</option><option value="80">Thermal 80 mm</option><option value="58">Thermal 58 mm</option></select></div>
        <button type="button" id="print-receipt" class="action-primary"><i class="fa-solid fa-print" aria-hidden="true"></i>Cetak / Simpan PDF</button>
        <a class="action-secondary" href="{{ route($type.'.create') }}">Transaksi berikutnya</a>
        <a class="action-secondary" href="{{ route('pos.scan', ['type' => $type]) }}">Scan barcode</a>
    </div>
    <article class="receipt-paper" aria-label="Struk {{ $transaction->kode_transaksi }}">
        <div class="receipt-brand"><div class="flex items-center gap-3"><img src="{{ asset('assets/logo.png') }}" alt="" width="48" height="48"><div><strong>STH NETWORK</strong><p>PT STH Network · Manajemen Inventaris</p></div></div><span class="receipt-kind {{ $incoming ? 'receipt-incoming' : 'receipt-outgoing' }}">{{ $incoming ? 'BARANG MASUK' : 'BARANG KELUAR' }}</span></div>
        <div class="receipt-heading"><div><p class="eyebrow">BUKTI TRANSAKSI</p><h2>{{ $incoming ? 'Penerimaan barang' : 'Pengeluaran barang' }}</h2></div><div class="receipt-number"><span>Nomor transaksi</span><strong>{{ $transaction->kode_transaksi }}</strong></div></div>
        <dl class="receipt-meta">
            <div><dt>Tanggal transaksi</dt><dd>{{ $transaction->tanggal->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Dicatat oleh</dt><dd>{{ $transaction->user->name ?? '-' }}</dd></div>
            <div><dt>{{ $incoming ? 'Supplier pemasok' : 'Tujuan / divisi penerima' }}</dt><dd>{{ $incoming ? ($transaction->supplier->nama ?? '-') : $transaction->tujuan }}</dd></div>
            <div><dt>Waktu pencatatan</dt><dd>{{ $transaction->created_at->format('d/m/Y H:i') }}</dd></div>
        </dl>
        <table class="receipt-items"><caption class="sr-only">Rincian barang dan jumlah</caption><thead><tr><th scope="col">Barang / kode</th><th scope="col" class="receipt-qty">Jumlah</th></tr></thead><tbody>
        @foreach($transaction->details as $detail)
            <tr><td><strong>{{ $detail->item->nama_barang ?? 'Barang tidak tersedia' }}</strong><span>{{ $detail->item->kode_barang ?? '-' }}</span></td><td class="receipt-qty"><strong>{{ number_format($detail->jumlah, 0, ',', '.') }}</strong> {{ $detail->item->satuan ?? 'unit' }}</td></tr>
        @endforeach
        </tbody></table>
        <div class="receipt-summary"><span>{{ $transaction->details->count() }} jenis barang</span><div><span>Total per satuan</span>@foreach($unitTotals as $unit => $total)<strong>{{ number_format($total, 0, ',', '.') }} {{ $unit }}</strong>@endforeach</div></div>
        <div class="receipt-note"><h3>Catatan transaksi</h3><p>{{ $transaction->keterangan ?: 'Tidak ada catatan tambahan.' }}</p></div>
        <div class="receipt-signatures"><div><span>{{ $incoming ? 'Diserahkan oleh' : 'Dikeluarkan oleh' }}</span><div></div><strong>{{ $incoming ? ($transaction->supplier->nama ?? 'Supplier') : ($transaction->user->name ?? 'Petugas') }}</strong></div><div><span>Diterima oleh</span><div></div><strong>{{ $incoming ? ($transaction->user->name ?? 'Petugas gudang') : $transaction->tujuan }}</strong></div></div>
        <footer class="receipt-footnote">Bukti transaksi inventaris · {{ $transaction->kode_transaksi }}<br>Simpan dokumen ini sebagai bukti serah terima barang.</footer>
    </article>
</div>
@endsection
