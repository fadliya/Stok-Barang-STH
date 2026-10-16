@extends('layouts.app')
@section('title', 'Scan Penerimaan Pesanan')
@section('content')
<div class="max-w-xl space-y-5">
    <h1 class="text-2xl font-extrabold">Scan Penerimaan Pesanan</h1>
    <p>Pastikan seluruh barang sudah tiba sesuai jumlah pada struk. Scan barcode struk dengan scanner, atau ketik nomor pesanan lalu tekan Enter. Seluruh rincian otomatis dicatat sebagai Barang Masuk dan stok bertambah.</p>
    @if($errors->any())<p role="alert" class="p-4 bg-red-50 text-red-800">{{ $errors->first() }}</p>@endif
    <form method="POST" action="{{ route('goods-requests.receive') }}" class="space-y-4">
        @csrf
        <label for="order-barcode" class="block font-bold">Barcode / nomor pesanan</label>
        <input id="order-barcode" name="barcode" value="{{ old('barcode') }}" required autofocus autocomplete="off" maxlength="100" class="w-full p-3 border rounded-xl bg-white dark:bg-slate-800">
        <button type="submit" class="px-5 py-3 bg-emerald-700 text-white rounded-xl">Terima Seluruh Barang</button>
    </form>
    <a href="{{ route('goods-requests.index') }}" class="underline">Kembali ke pemesanan</a>
</div>
@endsection
