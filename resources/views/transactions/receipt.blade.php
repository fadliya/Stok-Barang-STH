<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->kode_transaksi }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 24px auto; padding: 12px; max-width: 380px; font: 13px monospace; color: #111; background: white; }
        h1 { font-size: 18px; text-align: center; }
        h2 { font-size: 14px; text-align: center; }
        p, td { overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 8px 0; border-bottom: 1px dashed #888; vertical-align: top; }
        th:last-child, td:last-child { text-align: right; }
        .actions { display: flex; gap: 16px; align-items: center; margin-bottom: 24px; }
        button { padding: 10px; cursor: pointer; }
        @media print { @page { margin: 5mm; } body { margin: 0 auto; padding: 0; width: 100%; } .actions { display: none; } tr { break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route($type.'.index') }}">Kembali</a>
    </div>
    <h1>{{ config('app.name') }}</h1>
    <h2>Bukti Barang {{ $type === 'incoming' ? 'Masuk' : 'Keluar' }}</h2>
    <p>No: {{ $transaction->kode_transaksi }}<br>
        Tanggal: {{ $transaction->tanggal->format('d/m/Y H:i') }}<br>
        Petugas: {{ $transaction->user->name ?? '-' }}</p>
    <p>{{ $type === 'incoming' ? 'Supplier' : 'Tujuan' }}:
        {{ $type === 'incoming' ? ($transaction->supplier->nama ?? '-') : $transaction->tujuan }}</p>
    <table>
        <thead><tr><th>Barang</th><th>Jumlah</th></tr></thead>
        <tbody>
            @foreach($transaction->details as $detail)
                <tr><td>{{ $detail->item->nama_barang ?? 'Barang tidak tersedia' }}<br>
                    {{ $detail->item->kode_barang ?? '-' }}</td>
                    <td>{{ number_format($detail->jumlah) }} {{ $detail->item->satuan ?? '' }}</td></tr>
            @endforeach
        </tbody>
    </table>
    <p>Jenis barang: {{ $transaction->details->count() }}<br>Total jumlah: {{ number_format($transaction->total_quantity) }}</p>
    <p>Catatan: {{ $transaction->keterangan ?: '-' }}</p>
    <p style="text-align:center">Bukti transaksi inventaris</p>
</body>
</html>
