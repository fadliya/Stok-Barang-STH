@extends('layouts.app')
@section('title', 'Detail Surat Pemesanan')
@section('content')
<div class="space-y-6">
    <a class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-orange-600" href="{{ route('goods-requests.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Riwayat pemesanan</a>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div><h1 class="text-2xl font-bold">Detail Pemesanan</h1><p class="font-mono text-sm text-slate-500 mt-1">{{ $document->kode }}</p></div>
        <div class="order-actions">
            @if($document->status === 'approved' && !auth()->user()->isPimpinan())<a href="#pemeriksaan" class="order-button order-button-primary"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Periksa Barang</a>@endif
            @if($document->status !== 'pending')<a href="{{ route('goods-requests.print', $document) }}" class="order-button order-button-print"><i class="fa-solid fa-print" aria-hidden="true"></i> Cetak / Simpan PDF</a>@endif
        </div>
    </div>
    @include('goods-requests.approval')
    @if($errors->any())<div role="alert" class="p-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-200">{{ $errors->first() }}</div>@endif
    @if($document->status === 'pending' && auth()->user()->isPimpinan())<form method="POST" action="{{ route('goods-requests.approve', $document) }}">@csrf<button type="submit" class="order-button order-button-confirm"><i class="fa-solid fa-check" aria-hidden="true"></i> ACC Pemesanan</button></form>@endif
    <section class="order-panel"><div class="order-panel-heading"><h2>Informasi Pemesanan</h2><span class="order-badge">{{ $document->details->count() }} jenis barang</span></div><dl class="order-meta"><div><dt>Supplier</dt><dd>{{ $document->penerima }}</dd></div><div><dt>Pemohon</dt><dd>{{ $document->pemohon }}</dd></div><div><dt>Alamat supplier</dt><dd>{{ $document->alamat ?: '-' }}</dd></div><div><dt>Tanggal pengajuan</dt><dd>{{ $document->tanggal->format('d/m/Y') }}</dd></div><div class="sm:col-span-2"><dt>Catatan</dt><dd class="whitespace-pre-line">{{ $document->keterangan ?: 'Tidak ada catatan tambahan.' }}</dd></div></dl></section>
    @include('goods-requests.receiving')
    @if($document->status !== 'approved' || auth()->user()->isPimpinan())
    <section class="order-panel"><div class="order-panel-heading"><h2>Rincian Barang</h2></div><div class="overflow-x-auto"><table class="order-table"><thead><tr><th scope="col">Kode barang</th><th scope="col">Nama barang</th><th scope="col">Jumlah dipesan</th></tr></thead><tbody>@foreach($document->details as $detail)<tr><td class="font-mono text-xs">{{ $detail->kode_barang }}</td><td class="font-semibold">{{ $detail->nama_barang }}</td><td>{{ number_format($detail->jumlah) }} {{ $detail->satuan }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
</div>
@endsection
