@extends('layouts.app')
@section('title', 'Detail Surat Pemesanan')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-2xl font-extrabold">Surat Pemesanan {{ $document->kode }}</h1>@if($document->status !== 'pending')<a href="{{ route('goods-requests.print', $document) }}" class="px-5 py-3 bg-orange-600 text-white rounded-xl font-bold">Cetak Struk / Simpan PDF</a>@endif</div>
    @include('goods-requests.approval')
    @if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
    @if($document->status === 'pending' && auth()->user()->isPimpinan())
    <form method="POST" action="{{ route('goods-requests.approve', $document) }}">@csrf<button type="submit" class="px-5 py-3 bg-emerald-700 text-white rounded-xl">ACC Pemesanan</button></form>
    @endif
    @include('goods-requests.receiving')
    <div class="p-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
        <p><strong>Supplier:</strong> {{ $document->penerima }}</p><p><strong>Alamat:</strong> {{ $document->alamat ?: '-' }}</p>
        <p><strong>Tanggal:</strong> {{ $document->tanggal->format('d/m/Y') }}</p><p><strong>Pemohon:</strong> {{ $document->pemohon }}</p>
        <p class="whitespace-pre-line"><strong>Catatan:</strong> {{ $document->keterangan ?: '-' }}</p>
    </div>
    <div class="overflow-x-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
        <table class="w-full text-left text-sm"><thead><tr><th class="p-4">Kode barang</th><th class="p-4">Nama barang</th><th class="p-4">Jumlah dipesan</th></tr></thead><tbody>
            @foreach($document->details as $detail)<tr class="border-t border-slate-200 dark:border-slate-800"><td class="p-4">{{ $detail->kode_barang }}</td><td class="p-4">{{ $detail->nama_barang }}</td><td class="p-4">{{ number_format($detail->jumlah) }} {{ $detail->satuan }}</td></tr>@endforeach
        </tbody></table>
    </div>
    <p>Pemesanan yang disetujui tampil sebagai pending pada Barang Masuk. Stok bertambah setelah semua barang diperiksa dan penerimaan dikonfirmasi.</p>
    <div class="flex flex-wrap gap-4"><a href="{{ route('goods-requests.index') }}" class="underline">Riwayat pemesanan</a></div>
</div>
@endsection
