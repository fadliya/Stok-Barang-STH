@if($document->status === 'approved' && in_array(auth()->user()->role, ['admin', 'gudang']))
<form id="pemeriksaan" method="POST" action="{{ route('goods-requests.receive', $document) }}" class="order-panel" x-data="{ checked: @js(array_map('strval', old('checked', []))), total: {{ $document->details->count() }}, submitting: false }" @submit="submitting = true">
    @csrf
    <div class="order-panel-heading"><div><h2>Pemeriksaan Barang Masuk</h2><p>Cocokkan setiap barang dengan pesanan sebelum mencatat penerimaan.</p></div><span class="order-badge order-badge-pending"><i class="fa-regular fa-clock" aria-hidden="true"></i> Pending</span></div>
    <div class="p-4 sm:p-6 space-y-5">
        <div class="order-notice"><i class="fa-solid fa-circle-info mt-1" aria-hidden="true"></i><p>Stok belum bertambah. Pastikan jenis, jumlah, dan kondisi barang sesuai. Jika belum datang, kurang, atau rusak, biarkan pesanan pending.</p></div>
        <fieldset class="space-y-3"><legend class="font-bold mb-3">Barang yang perlu diperiksa</legend>
        @foreach($document->details as $detail)
        <label class="order-check"><input type="checkbox" name="checked[]" value="{{ $detail->id }}" required x-model="checked" @checked(in_array($detail->id, old('checked', [])))><span class="order-check-info"><strong>{{ $detail->nama_barang }}</strong><small>{{ $detail->kode_barang }} &middot; Barang sudah diperiksa dan sesuai.</small></span><span class="order-check-quantity">{{ number_format($detail->jumlah) }} {{ $detail->satuan }}</span></label>
        @endforeach
        </fieldset>
        <div class="space-y-2" aria-live="polite"><p class="text-sm text-slate-500"><span x-text="checked.length">0</span> dari {{ $document->details->count() }} jenis barang sudah diperiksa</p><div class="order-progress" aria-hidden="true"><span :style="{ width: (total ? checked.length / total * 100 : 0) + '%' }"></span></div></div>
    </div>
    <div class="p-4 sm:p-6 border-t border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4"><p class="text-sm text-slate-500 max-w-md">Konfirmasi mencatat tanggal penerimaan dan menambah stok sesuai jumlah pesanan.</p><button type="submit" class="order-button order-button-confirm" :disabled="checked.length !== total || submitting"><i class="fa-solid fa-check-double" aria-hidden="true"></i><span x-text="submitting ? 'Menyimpan...' : 'Konfirmasi Semua Barang Diterima'">Konfirmasi Semua Barang Diterima</span></button></div>
</form>
@endif
