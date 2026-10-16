@extends('layouts.app')
@section('title', 'Buat Surat Pemesanan')
@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-extrabold">Buat Surat Pemesanan</h1><p class="text-sm text-slate-500">Pilih supplier, ceklis barang yang diperlukan, lalu isi jumlah pesanan.</p></div>
    <form method="GET" action="{{ route('goods-requests.create') }}" class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3">
        <label for="supplier_id" class="block font-bold">Supplier</label>
        <div class="flex flex-wrap gap-3">
            <select name="supplier_id" id="supplier_id" required class="flex-1 min-w-48 p-3 border rounded-xl bg-white dark:bg-slate-800">
                <option value="">Pilih supplier</option>
                @foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected($supplierId == $supplier->id)>{{ $supplier->nama }}</option>@endforeach
            </select>
            <button type="submit" class="px-4 py-3 bg-slate-800 text-white rounded-xl">Tampilkan Barang</button>
        </div>
        <p class="text-sm text-slate-500">Mengganti supplier akan memuat ulang daftar dan mengosongkan pilihan barang.</p>
        @if($suppliers->isEmpty())<p>Belum ada supplier. Minta admin menambahkan data supplier terlebih dahulu.</p>@endif
    </form>
    @if($errors->any())
        <div role="alert" class="p-4 bg-red-50 text-red-800 rounded-xl"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if($supplierId)
    <form method="POST" action="{{ route('goods-requests.store') }}" class="space-y-5" x-data="{ search: '' }">
        @csrf
        <input type="hidden" name="supplier_id" value="{{ $supplierId }}">
        <div class="grid md:grid-cols-2 gap-4">
            <div><label for="tanggal" class="block font-bold mb-2">Tanggal surat</label><input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800"></div>
            <div><label for="keterangan" class="block font-bold mb-2">Catatan (opsional)</label><textarea id="keterangan" name="keterangan" maxlength="5000" class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800">{{ old('keterangan') }}</textarea></div>
        </div>
        <div><label for="search-items" class="block font-bold mb-2">Cari barang</label><input type="search" id="search-items" x-model="search" placeholder="Nama atau kode barang" class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800"></div>
        <p class="text-sm text-slate-500">Semua barang supplier ditampilkan. Ceklis hanya barang yang akan dipesan. Jumlah barang yang tidak diceklis akan diabaikan. Pencarian tidak menghapus pilihan sebelumnya.</p>
        <div class="overflow-x-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
            <table class="w-full text-left text-sm">
                <thead><tr><th class="p-4">Pilih</th><th class="p-4">Kode / Nama barang</th><th class="p-4">Stok saat ini</th><th class="p-4">Stok minimum</th><th class="p-4">Jumlah pesanan</th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="border-t border-slate-200 dark:border-slate-800" x-data="{ label: @js(strtolower($item->kode_barang.' '.$item->nama_barang)) }" x-show="label.includes(search.toLowerCase())">
                        <td class="p-4"><input type="checkbox" name="selected[]" value="{{ $item->id }}" id="item-{{ $item->id }}" @checked(in_array($item->id, old('selected', []))) class="w-5 h-5"></td>
                        <td class="p-4"><label for="item-{{ $item->id }}" class="cursor-pointer"><span class="text-slate-500">{{ $item->kode_barang }}</span><br>{{ $item->nama_barang }}</label></td>
                        <td class="p-4">{{ $item->stok }} {{ $item->satuan }}</td><td class="p-4">{{ $item->minimum_stok }} {{ $item->satuan }}</td>
                        <td class="p-4"><label for="qty-{{ $item->id }}" class="sr-only">Jumlah pesanan {{ $item->nama_barang }}</label><input id="qty-{{ $item->id }}" name="quantities[{{ $item->id }}]" type="number" min="1" max="2147483647" step="1" value="{{ old('quantities.'.$item->id) }}" class="w-28 p-2 border rounded-lg bg-white dark:bg-slate-800"> {{ $item->satuan }}</td>
                    </tr>
                    @empty<tr><td colspan="5" class="p-8 text-center">Belum ada barang untuk supplier ini. Tambahkan atau sesuaikan supplier pada data barang terlebih dahulu.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-4 items-center"><button type="submit" @disabled($items->isEmpty()) class="px-5 py-3 rounded-xl bg-orange-600 text-white font-bold disabled:opacity-50">Ajukan ke Pimpinan</button><a href="{{ route('goods-requests.index') }}" class="underline">Kembali ke riwayat</a></div>
    </form>
    @endif
</div>
@endsection
