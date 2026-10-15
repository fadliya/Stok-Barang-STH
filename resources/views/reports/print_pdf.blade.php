<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - PT STH Network</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #c4510b;
            padding-bottom: 12px;
            margin-bottom: 20px;
            position: relative;
        }
        .header img {
            height: 45px;
            position: absolute;
            left: 10px;
            top: 0;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 13px;
            font-weight: 700;
            margin: 3px 0 0 0;
            color: #c4510b;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10px;
            color: #64748b;
            margin: 4px 0 0 0;
        }
        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h3 {
            font-size: 15px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            color: #0f172a;
        }
        .report-title p {
            font-size: 10px;
            color: #64748b;
            margin: 3px 0 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #fff8f0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            color: #804000;
            border-bottom: 2px solid #c4510b;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: 700; }
        .footer-sig {
            margin-top: 40px;
            width: 100%;
        }
        .sig-box {
            float: right;
            width: 220px;
            text-align: center;
        }
        .sig-space {
            height: 70px;
        }
        @media print {
            @page { margin: 15mm; size: A4 landscape; }
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #c4510b; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="header">
        <img src="{{ asset('assets/logo.png') }}" alt="Logo PT STH Network">
        <h1>PT STH NETWORK</h1>
        <h2>Aplikasi Stok Barang & Inventaris Gudang</h2>
        <p>Instagram: @sthnetwork.id (https://www.instagram.com/sthnetwork.id) | Email: info@sthnetwork.id</p>
    </div>

    <!-- Title -->
    <div class="report-title">
        <h3>{{ $title }}</h3>
        <p>Dicetak Pada: {{ date('d F Y - H:i') }} WIB | Oleh: {{ auth()->user()->name }} ({{ strtoupper(auth()->user()->role) }})</p>
    </div>

    <!-- Content Table -->
    @if($type === 'incoming')
    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Kode TRX</th>
                <th class="text-center">Tanggal</th>
                <th>Supplier Pemasok</th>
                <th>Barcode / Kode Barang</th>
                <th>Nama Barang</th>
                <th class="text-right">Jumlah Masuk</th>
                <th>Satuan</th>
                <th>Staf Pemroses</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($data as $tx)
                @foreach($tx->details as $detail)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center font-mono font-bold">{{ $tx->kode_transaksi }}</td>
                    <td class="text-center font-mono">{{ $tx->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $tx->supplier->nama ?? '-' }}</td>
                    <td class="font-mono font-bold">{{ $detail->item->barcode ?? $detail->item->kode_barang ?? '-' }}</td>
                    <td class="font-bold">{{ $detail->item->nama_barang ?? '-' }}</td>
                    <td class="text-right font-bold" style="color: #047857;">+{{ number_format($detail->jumlah) }}</td>
                    <td>{{ $detail->item->satuan ?? '-' }}</td>
                    <td>{{ $tx->user->name ?? '-' }}</td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data barang masuk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @elseif($type === 'outgoing')
    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Kode TRX</th>
                <th class="text-center">Tanggal</th>
                <th>Tujuan / Divisi</th>
                <th>Barcode / Kode Barang</th>
                <th>Nama Barang</th>
                <th class="text-right">Jumlah Keluar</th>
                <th>Satuan</th>
                <th>Staf Pemroses</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($data as $tx)
                @foreach($tx->details as $detail)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center font-mono font-bold">{{ $tx->kode_transaksi }}</td>
                    <td class="text-center font-mono">{{ $tx->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $tx->tujuan }}</td>
                    <td class="font-mono font-bold">{{ $detail->item->barcode ?? $detail->item->kode_barang ?? '-' }}</td>
                    <td class="font-bold">{{ $detail->item->nama_barang ?? '-' }}</td>
                    <td class="text-right font-bold" style="color: #e11d48;">-{{ number_format($detail->jumlah) }}</td>
                    <td>{{ $detail->item->satuan ?? '-' }}</td>
                    <td>{{ $tx->user->name ?? '-' }}</td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data barang keluar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @else
    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Barcode</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Supplier</th>
                <th class="text-right">Stok Sisa</th>
                <th class="text-right">Min Stok</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono font-bold" style="color: #c4510b;">{{ $row->barcode ?? '-' }}</td>
                <td class="font-mono font-bold">{{ $row->kode_barang }}</td>
                <td class="font-bold">{{ $row->nama_barang }}</td>
                <td>{{ $row->category->nama ?? '-' }}</td>
                <td>{{ $row->supplier->nama ?? '-' }}</td>
                <td class="text-right font-bold">{{ number_format($row->stok) }} {{ $row->satuan }}</td>
                <td class="text-right">{{ number_format($row->minimum_stok) }} {{ $row->satuan }}</td>
                <td class="text-center font-bold" style="color: {{ $row->stok <= $row->minimum_stok ? '#e11d48' : '#047857' }};">
                    {{ $row->stok <= $row->minimum_stok ? 'STOK HAMPIR HABIS' : 'NORMAL' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Belum ada data barang terdaftar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- Signature -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Medan, {{ date('d F Y') }}</p>
            <p class="font-bold">Mengetahui & Menyetujui,</p>
            <div class="sig-space"></div>
            <p class="font-bold" style="text-decoration: underline;">( ............................................ )</p>
            <p style="font-size: 11px; color: #64748b;">Pimpinan / Manajer Gudang</p>
        </div>
    </div>

    <script>
        if (window.name === '_blank' || window.location.search.includes('print=true')) {
            window.onload = function() {
                window.print();
            }
        }
    </script>
</body>
</html>
