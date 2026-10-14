@extends('layouts.app')
@section('title', 'Stok Opname')
@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-extrabold">Stok Opname</h1><p class="text-sm text-slate-500">Catat hasil penghitungan fisik. Stok sistem akan diganti sesuai hasil opname.</p></div>
    @if($errors->any())
        <div role="alert" class="p-4 rounded-xl bg-red-50 text-red-800">
            <p class="font-bold">Periksa kembali data opname:</p>
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ route('stock-opname.store') }}" class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 space-y-4">
        @csrf
        <div><label for="item_id" class="block font-bold">Barang</label>
            <select id="item_id" name="item_id" required class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800">
                <option value="">Pilih barang</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>{{ $item->kode_barang }} - {{ $item->nama_barang }} (stok: {{ $item->stok }} {{ $item->satuan }})</option>
                @endforeach
            </select>
        </div>
        <p class="text-sm text-slate-500">Selisih dihitung dari stok sistem saat opname disimpan.</p>
        <div><label for="stok_sesudah" class="block font-bold">Stok fisik</label><input id="stok_sesudah" name="stok_sesudah" type="number" min="0" max="2147483647" value="{{ old('stok_sesudah') }}" required class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800"></div>
        <div><label for="tanggal" class="block font-bold">Tanggal opname</label><input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800"></div>
        <div><label for="alasan" class="block font-bold">Alasan penyesuaian</label><input id="alasan" name="alasan" maxlength="255" value="{{ old('alasan', 'Selisih Opname') }}" required class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800"></div>
        <div><label for="keterangan" class="block font-bold">Keterangan (opsional)</label><textarea id="keterangan" name="keterangan" maxlength="5000" class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800">{{ old('keterangan') }}</textarea></div>
        <button type="submit" @disabled($items->isEmpty()) class="px-5 py-3 bg-orange-600 text-white rounded-xl font-bold disabled:opacity-50">Simpan Stok Opname</button>
        @if($items->isEmpty())<p>Belum ada barang. Tambahkan data barang terlebih dahulu.</p>@endif
    </form>
    <h2 class="text-lg font-bold">Riwayat Stok Opname</h2>
    <div class="overflow-x-auto bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
        <table class="w-full text-left text-sm">
            <thead><tr><th class="p-3">Kode / Tanggal</th><th class="p-3">Barang</th><th class="p-3">Stok sistem</th><th class="p-3">Stok fisik</th><th class="p-3">Selisih</th><th class="p-3">Alasan / Catatan</th><th class="p-3">Petugas</th></tr></thead>
            <tbody>
                @forelse($adjustments as $adjustment)
                <tr class="border-t border-slate-200 dark:border-slate-800">
                    <td class="p-3">{{ $adjustment->kode_penyesuaian }}<br>{{ $adjustment->tanggal->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $adjustment->item->nama_barang ?? '-' }}</td>
                    <td class="p-3">{{ $adjustment->stok_sebelumnya }}</td><td class="p-3">{{ $adjustment->stok_sesudah }}</td>
                    <td class="p-3">{{ $adjustment->stok_sesudah - $adjustment->stok_sebelumnya }}</td>
                    <td class="p-3">{{ $adjustment->alasan }}<br>{{ $adjustment->keterangan }}</td><td class="p-3">{{ $adjustment->user->name ?? '-' }}</td>
                </tr>
                @empty<tr><td colspan="7" class="p-6 text-center">Belum ada riwayat stok opname.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    {{ $adjustments->links() }}
</div>
@endsection
