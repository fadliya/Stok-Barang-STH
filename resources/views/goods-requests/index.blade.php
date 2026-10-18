@extends('layouts.app')
@section('title', 'Pemesanan ke Supplier')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-start gap-4"><div><h1 class="text-2xl font-bold">Pemesanan ke Supplier</h1><p class="text-sm text-slate-500 mt-1">Pantau pengajuan, persetujuan pimpinan, dan penerimaan barang.</p></div>
        @if(!auth()->user()->isPimpinan())<div class="order-actions"><a href="{{ route('incoming.index') }}#pending" class="order-button"><i class="fa-regular fa-clock" aria-hidden="true"></i> Barang Masuk / Pending</a><a href="{{ route('goods-requests.create') }}" class="order-button order-button-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Buat Surat Pemesanan</a></div>@endif
    </div>
    <div class="order-panel overflow-x-auto"><table class="order-table"><thead><tr><th scope="col">Nomor / tanggal</th><th scope="col">Supplier</th><th scope="col">Jenis barang</th><th scope="col">Pemohon</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead><tbody>
    @forelse($requests as $document)
    <tr><td><span class="font-mono text-xs font-semibold">{{ $document->kode }}</span><span class="block text-xs text-slate-500 mt-1">{{ $document->tanggal->format('d/m/Y') }}</span></td><td class="font-semibold">{{ $document->penerima }}</td><td>{{ $document->details_count }} jenis</td><td>{{ $document->pemohon }}</td><td><span class="order-badge {{ $document->status === 'received' ? 'order-badge-success' : 'order-badge-pending' }}"><i class="fa-solid {{ $document->status === 'received' ? 'fa-check' : 'fa-clock' }}" aria-hidden="true"></i>{{ ['pending' => 'Menunggu ACC', 'approved' => 'Menunggu barang', 'received' => 'Diterima'][$document->status] }}</span></td><td><div class="order-actions"><a href="{{ route('goods-requests.show', $document) }}" class="order-button" aria-label="Detail {{ $document->kode }}"><i class="fa-regular fa-eye" aria-hidden="true"></i> Detail</a>@if($document->status !== 'pending')<a href="{{ route('goods-requests.print', $document) }}" class="order-button order-button-print" aria-label="Cetak {{ $document->kode }}"><i class="fa-solid fa-print" aria-hidden="true"></i> Cetak</a>@endif</div></td></tr>
    @empty<tr><td colspan="6"><div class="order-empty"><i class="fa-regular fa-file-lines" aria-hidden="true"></i><p class="font-semibold">Belum ada pemesanan</p><p class="text-sm mt-1">Pengajuan baru akan muncul di sini untuk diproses pimpinan.</p></div></td></tr>@endforelse
    </tbody></table></div>
    {{ $requests->links() }}
</div>
@endsection
