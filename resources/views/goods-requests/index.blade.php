@extends('layouts.app')
@section('title', 'Pemesanan ke Supplier')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div><h1 class="text-2xl font-extrabold">Pemesanan ke Supplier</h1><p class="text-sm text-slate-500">Riwayat surat pemesanan barang. Stok dicatat terpisah saat barang diterima.</p></div>
        <a href="{{ route('goods-requests.create') }}" class="px-5 py-3 bg-orange-600 text-white rounded-2xl font-bold">Buat Surat Pemesanan</a>
    </div>
    <div class="overflow-x-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
        <table class="w-full text-left text-sm">
            <thead><tr><th class="p-4">Nomor surat</th><th class="p-4">Tanggal</th><th class="p-4">Supplier</th><th class="p-4">Jenis barang</th><th class="p-4">Pemohon</th><th class="p-4">Aksi</th></tr></thead>
            <tbody>
                @forelse($requests as $document)
                    <tr class="border-t border-slate-200 dark:border-slate-800">
                        <td class="p-4">{{ $document->kode }}</td><td class="p-4">{{ $document->tanggal->format('d/m/Y') }}</td>
                        <td class="p-4">{{ $document->penerima }}</td><td class="p-4">{{ $document->details_count }}</td><td class="p-4">{{ $document->pemohon }}</td>
                        <td class="p-4"><a href="{{ route('goods-requests.show', $document) }}" class="text-orange-600 underline">Detail</a> · <a href="{{ route('goods-requests.print', $document) }}" class="text-orange-600 underline">Cetak</a></td>
                    </tr>
                @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada surat pemesanan ke supplier.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
</div>
@endsection
