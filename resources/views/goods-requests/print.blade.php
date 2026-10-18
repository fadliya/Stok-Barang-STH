<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Struk Pemesanan {{ $document->kode }}</title>
    <style>
        :root { --ink: #17212f; --muted: #526071; --accent: #c4510b; --line: #cbd2d9; --canvas: #eef1f4; --paper: #fff; --success: #14623c; --s1: 4px; --s2: 8px; --s3: 12px; --s4: 16px; --s5: 24px; --s6: 32px; --s7: 40px; }
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; padding: var(--s5) var(--s4) var(--s7); background: var(--canvas); color: var(--ink); font: 13px/1.5 Arial, Helvetica, sans-serif; }
        h1, h2, p { margin: 0; }
        a, button { font: inherit; }
        a:focus-visible, button:focus-visible, .table-wrap:focus-visible, .barcode-wrap:focus-visible { outline: 3px solid var(--ink); outline-offset: 4px; }
        .toolbar { max-width: 210mm; margin: 0 auto var(--s4); display: flex; justify-content: space-between; align-items: center; gap: var(--s4); }
        .toolbar a { color: var(--muted); text-underline-offset: 4px; }
        .toolbar button { padding: var(--s3) var(--s4); border: 1px solid var(--accent); border-radius: 4px; background: var(--accent); color: var(--paper); cursor: pointer; font-weight: 700; }
        .toolbar button:hover { background: var(--ink); border-color: var(--ink); }
        .paper { max-width: 210mm; margin: 0 auto; padding: 36px; background: var(--paper); border: 1px solid var(--line); border-radius: 4px; box-shadow: 0 8px 32px #17212f0d; }
        .masthead { display: flex; justify-content: space-between; align-items: flex-start; gap: var(--s5); padding-bottom: var(--s5); border-bottom: 3px solid var(--accent); }
        .brand { font-size: 22px; line-height: 1.2; letter-spacing: -.6px; font-weight: 800; }
        .brand span { color: var(--accent); }
        .brand-subtitle { color: var(--muted); margin-top: var(--s1); font-size: 11px; }
        .document-id { text-align: right; min-width: 0; }
        .label { display: block; color: var(--muted); font-size: 10px; line-height: 1.4; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 700; }
        .order-number { display: block; margin-top: var(--s2); font: 700 12px/1.5 Consolas, monospace; overflow-wrap: anywhere; }
        .title-row { display: flex; justify-content: space-between; align-items: center; gap: var(--s4); margin: var(--s5) 0; }
        h1 { font-size: 27px; line-height: 1.1; letter-spacing: -.8px; }
        .subtitle { color: var(--muted); margin-top: var(--s2); font-size: 12px; }
        .status { color: var(--success); border: 1px solid var(--success); padding: 6px 10px; font-size: 10px; font-weight: 700; letter-spacing: .6px; white-space: nowrap; border-radius: 3px; }
        .recipient { display: grid; grid-template-columns: 1fr 190px; gap: var(--s5); padding: var(--s4) 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .recipient > div { min-width: 0; }
        .recipient strong { display: block; margin-top: var(--s2); font-size: 15px; overflow-wrap: anywhere; }
        .address { margin-top: var(--s1); color: var(--muted); white-space: pre-line; overflow-wrap: anywhere; }
        .date { margin: var(--s2) 0 var(--s3); font-weight: 700; }
        .intro { margin: var(--s5) 0 var(--s4); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 12px; }
        caption { text-align: left; font-size: 10px; line-height: 1.4; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 700; margin-bottom: var(--s3); color: var(--muted); }
        th { background: #f3f5f7; font-size: 10px; letter-spacing: .5px; text-transform: uppercase; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        th, td { text-align: left; padding: var(--s3) var(--s2); vertical-align: top; overflow-wrap: anywhere; }
        td { border-bottom: 1px solid var(--line); }
        .col-no { width: 7%; color: var(--muted); }
        .col-code { width: 23%; }
        .col-name { width: 40%; }
        .col-qty { width: 15%; text-align: right; font-variant-numeric: tabular-nums; }
        .col-unit { width: 15%; }
        td.col-code { font-family: Consolas, monospace; font-size: 11px; }
        td.col-name, td.col-qty { font-weight: 700; }
        tr { break-inside: avoid; }
        .table-summary { text-align: right; padding-top: var(--s3); color: var(--muted); font-size: 11px; }
        .table-summary strong { color: var(--ink); }
        .notes { margin-top: var(--s5); padding-left: var(--s3); border-left: 2px solid var(--accent); }
        .notes p { margin-top: var(--s2); white-space: pre-line; overflow-wrap: anywhere; }
        .closing { margin-top: var(--s4); color: var(--muted); font-size: 12px; }
        .approvals { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: var(--s5); padding-top: var(--s4); border-top: 1px solid var(--line); break-inside: avoid; }
        .approval { padding: 0 var(--s4); border-left: 1px solid var(--line); min-width: 0; }
        .approval:first-child { padding-left: 0; border-left: 0; }
        .approval:last-child { padding-right: 0; }
        .approval h2 { font-size: 10px; line-height: 1.4; letter-spacing: .7px; text-transform: uppercase; color: var(--muted); }
        .approval .approval-state { display: block; margin-top: var(--s3); color: var(--success); font-size: 11px; font-weight: 700; }
        .approval .name { display: block; margin-top: var(--s2); font-size: 12px; overflow-wrap: anywhere; }
        .approval time { display: block; margin-top: var(--s1); color: var(--muted); font-size: 10px; }
        .approval-note { margin-top: var(--s3); font-size: 10px; color: var(--muted); }
        .receiving { margin-top: var(--s5); padding-top: var(--s4); border-top: 1px dashed var(--muted); break-inside: avoid; }
        .receiving h2 { font-size: 12px; }
        .barcode-wrap { overflow-x: auto; margin: var(--s3) 0 var(--s2); }
        .barcode-svg { display: block; margin: auto; max-width: none; }
        .scan-help { text-align: center; color: var(--muted); font-size: 11px; }
        .received-at { margin-top: var(--s2); text-align: center; font-size: 11px; color: var(--success); font-weight: 700; }
        .footer { margin-top: var(--s5); padding-top: var(--s3); border-top: 1px solid var(--line); display: flex; justify-content: space-between; gap: var(--s4); color: var(--muted); font-size: 9px; }
        @media screen and (max-width: 680px) {
            .paper { padding: var(--s5); }
            .masthead { display: block; }
            .document-id { text-align: left; margin-top: var(--s4); }
            .title-row { display: block; }
            .status { display: inline-block; margin-top: var(--s3); }
            .recipient { grid-template-columns: 1fr; gap: var(--s4); }
            .approvals { grid-template-columns: 1fr; gap: var(--s4); }
            .approval, .approval:first-child, .approval:last-child { padding: 0 0 var(--s4); border-left: 0; border-bottom: 1px solid var(--line); }
            .approval:last-child { padding-bottom: 0; border-bottom: 0; }
            table { min-width: 490px; }
            .footer { display: block; }
            .footer span { display: block; margin-top: var(--s1); }
        }
        @media screen and (max-width: 480px) {
            body { padding-top: var(--s4); }
            .paper { padding: 20px; box-shadow: none; }
            .toolbar { align-items: stretch; font-size: 12px; }
            .toolbar a { align-self: center; }
            .toolbar button { padding: 10px; }
            h1 { font-size: 25px; }
        }
        @media print {
            @page { size: A4; margin: 12mm; }
            body { padding: 0; margin: 0; background: var(--paper); font-size: 9pt; line-height: 1.4; }
            .toolbar { display: none; }
            .paper { max-width: none; padding: 0; border: 0; border-radius: 0; box-shadow: none; }
            .masthead { padding-bottom: var(--s3); }
            .title-row { margin: var(--s3) 0; }
            .recipient { padding: var(--s3) 0; }
            .intro { margin: var(--s3) 0; }
            .table-wrap, .barcode-wrap { overflow: visible; }
            table { min-width: 0; }
            thead { display: table-header-group; }
            th { background: var(--paper); }
            th, td { padding-top: var(--s2); padding-bottom: var(--s2); }
            .notes, .closing, .approvals, .receiving, .footer { margin-top: var(--s3); }
            .approvals, .receiving { padding-top: var(--s3); }
            .approval .approval-state { margin-top: var(--s2); }
            .approval-note, .barcode-wrap { margin-top: var(--s2); }
            .brand, h1, .recipient { break-inside: avoid; }
            .footer { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <nav class="toolbar" aria-label="Tindakan struk">
        <a href="{{ route('goods-requests.show', $document) }}">&larr; Kembali ke detail</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </nav>
    <main class="paper">
        <header class="masthead">
            <div><p class="brand">STH<span> NETWORK</span></p><p class="brand-subtitle">PT STH NETWORK &middot; Sistem Stok Barang</p></div>
            <div class="document-id"><span class="label">Nomor pemesanan</span><strong class="order-number">{{ $document->kode }}</strong></div>
        </header>
        <div class="title-row">
            <div><h1>Struk Pemesanan</h1><p class="subtitle">Surat permintaan / pemesanan barang</p></div>
            <span class="status">{{ $document->status === 'received' ? 'BARANG DITERIMA' : 'DISETUJUI PIMPINAN' }}</span>
        </div>
        <section class="recipient" aria-label="Supplier dan tanggal pemesanan">
            <div><span class="label">Kepada supplier</span><strong>{{ $document->penerima }}</strong><p class="address">{{ $document->alamat ?: 'Di tempat' }}</p></div>
            <div><span class="label">Tanggal pemesanan</span><p class="date">{{ $document->tanggal->format('d/m/Y') }}</p><span class="label">Diajukan oleh</span><p>{{ $document->pemohon }}</p></div>
        </section>
        <p class="intro">Dengan hormat,<br>Mohon disediakan barang sesuai rincian pemesanan berikut:</p>
        <div class="table-wrap" role="region" aria-label="Rincian barang pesanan, geser jika diperlukan" tabindex="0">
            <table>
                <caption>Rincian barang</caption>
                <thead><tr><th scope="col" class="col-no">No.</th><th scope="col" class="col-code">Kode barang</th><th scope="col" class="col-name">Nama barang</th><th scope="col" class="col-qty">Jumlah</th><th scope="col" class="col-unit">Satuan</th></tr></thead>
                <tbody>
                    @foreach($document->details as $detail)
                    <tr><td class="col-no">{{ $loop->iteration }}</td><td class="col-code">{{ $detail->kode_barang }}</td><td class="col-name">{{ $detail->nama_barang }}</td><td class="col-qty">{{ number_format($detail->jumlah, 0, ',', '.') }}</td><td class="col-unit">{{ $detail->satuan }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="table-summary">Total jenis barang: <strong>{{ $document->details->count() }}</strong></p>
        @if($document->keterangan)
        <section class="notes" aria-label="Catatan pemesanan"><h2 class="label">Catatan</h2><p>{{ $document->keterangan }}</p></section>
        @endif
        <p class="closing">Demikian surat pemesanan ini kami sampaikan. Terima kasih atas kerja sama Anda.</p>
        <section class="approvals" aria-label="Bukti pengajuan dan persetujuan sistem">
            <div class="approval"><h2>Mengajukan</h2><span class="approval-state">Tercatat di sistem</span><strong class="name">{{ $document->pemohon }}</strong><time datetime="{{ $document->created_at->toIso8601String() }}">{{ $document->created_at->format('d/m/Y H:i') }}</time></div>
            @foreach(['Mengetahui', 'Menyetujui'] as $responsibility)
            <div class="approval"><h2>{{ $responsibility }}</h2><span class="approval-state">ACC sistem &middot; Pimpinan</span><strong class="name">{{ $document->approver_name }}</strong><time datetime="{{ $document->approved_at->toIso8601String() }}">{{ $document->approved_at->format('d/m/Y H:i') }}</time></div>
            @endforeach
        </section>
        <p class="approval-note">Bukti persetujuan sistem: {{ $document->kode }}. Tidak memerlukan tanda tangan manual.</p>
        <section class="receiving" aria-label="Tanda tangan QR persetujuan pimpinan">
            <h2>Disetujui oleh Pimpinan</h2>
            <canvas class="approval-qr" data-value="{{ $verificationUrl }}" role="img" aria-label="QR verifikasi persetujuan {{ $document->kode }}"></canvas>
            <p><strong>{{ $document->approver_name }}</strong><br>{{ $document->approved_at->format('d/m/Y H:i') }}</p>
            <p class="scan-help">Pindai QR untuk melihat bukti ACC dan daftar barang yang disetujui.</p>
            <p><a href="{{ $verificationUrl }}">Lihat bukti persetujuan</a></p>
            @if($document->received_at)<p class="received-at">Penerimaan tercatat pada {{ $document->received_at->format('d/m/Y H:i') }}</p>@endif
        </section>        <footer class="footer"><span>PT STH NETWORK &middot; Dokumen pemesanan supplier</span><span>Dicetak dari Sistem Stok Barang</span></footer>
    </main>
    @vite(['resources/js/approval-qr.js'])
</body>
</html>
