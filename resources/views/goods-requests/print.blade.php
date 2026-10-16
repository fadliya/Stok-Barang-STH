<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Pemesanan {{ $document->kode }}</title>
    <style>
        * { box-sizing: border-box; } body { max-width: 800px; margin: 24px auto; padding: 24px; font: 14px/1.6 Arial, sans-serif; color: #111; }
        header { border-bottom: 3px solid #111; text-align: center; padding-bottom: 12px; } header strong { font-size: 22px; }
        h1 { font-size: 18px; text-align: center; text-decoration: underline; margin-bottom: 0; }
        .number { text-align: center; margin-top: 0; } table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; overflow-wrap: anywhere; } tr { break-inside: avoid; }
        .barcode-svg { max-width: 100%; height: auto; break-inside: avoid; }
        .actions { display: flex; gap: 20px; align-items: center; margin-bottom: 24px; } button { padding: 10px; cursor: pointer; }
        .notes { white-space: pre-line; overflow-wrap: anywhere; }
        @media print { @page { size: A4; margin: 18mm; } body { margin: 0; padding: 0; max-width: none; } .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button><a href="{{ route('goods-requests.show', $document) }}">Kembali ke detail</a></div>
    <header><strong>PT STH NETWORK</strong><div>Sistem Stok Barang</div></header>
    <h1>SURAT PERMINTAAN / PEMESANAN BARANG</h1><p class="number">Nomor: {{ $document->kode }}</p>
    <p>Tanggal: {{ $document->tanggal->format('d/m/Y') }}</p>
    <p>Kepada Yth.<br><strong>{{ $document->penerima }}</strong><br><span class="notes">{{ $document->alamat ?: 'Di tempat' }}</span></p>
    <p>Dengan hormat,<br>Mohon disediakan barang sesuai rincian pemesanan berikut:</p>
    <table><thead><tr><th>No.</th><th>Kode barang</th><th>Nama barang</th><th>Jumlah</th><th>Satuan</th></tr></thead><tbody>
        @foreach($document->details as $detail)<tr><td>{{ $loop->iteration }}</td><td>{{ $detail->kode_barang }}</td><td>{{ $detail->nama_barang }}</td><td>{{ number_format($detail->jumlah) }}</td><td>{{ $detail->satuan }}</td></tr>@endforeach
    </tbody></table>
    @if($document->keterangan)<p class="notes"><strong>Catatan:</strong><br>{{ $document->keterangan }}</p>@endif
    <p>Demikian surat pemesanan ini kami sampaikan. Terima kasih atas kerja sama Anda.</p>
    @include('goods-requests.approval')
    <p>Barcode penerimaan: scan setelah seluruh barang tiba sesuai jumlah pesanan.</p>
    <svg class="barcode-svg" data-value="{{ $document->kode }}"></svg>
    @vite(['resources/js/order-barcode.js'])
</body>
</html>
