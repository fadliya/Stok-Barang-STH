@if($document->status === 'approved' && in_array(auth()->user()->role, ['admin', 'gudang']))
<form method="POST" action="{{ route('goods-requests.receive', $document) }}" class="p-6 bg-white dark:bg-slate-900 border rounded-2xl space-y-4">
    @csrf
    <h2 class="font-bold text-lg">Pemeriksaan Barang Masuk — Pending</h2>
    <p>Stok belum bertambah. Ceklis hanya setelah jenis, jumlah, dan kondisi barang sesuai pengajuan. Jika belum datang, kurang, atau rusak, biarkan pending sampai sesuai.</p>
    @foreach($document->details as $detail)
    <label class="flex items-start gap-3 border-b p-3">
        <input type="checkbox" name="checked[]" value="{{ $detail->id }}" required class="mt-1" @checked(in_array($detail->id, old('checked', [])))>
        <span>{{ $detail->kode_barang }} — {{ $detail->nama_barang }}: <strong>{{ $detail->jumlah }} {{ $detail->satuan }}</strong>. Barang sudah diterima dan sesuai.</span>
    </label>
    @endforeach
    <button type="submit" class="px-5 py-3 bg-emerald-700 text-white rounded-xl font-bold">Konfirmasi Semua Barang Diterima</button>
    <p class="text-sm">Konfirmasi akan mencatat tanggal penerimaan dan menambah stok satu kali.</p>
</form>
@endif
