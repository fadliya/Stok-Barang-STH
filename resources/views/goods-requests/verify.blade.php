<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Verifikasi persetujuan {{ $document->kode }}</title>
<style>body{font:16px/1.6 Arial,sans-serif;background:#f1f5f9;color:#17212f;margin:0;padding:24px}main{max-width:760px;margin:auto;background:white;padding:28px;border-radius:12px}h1{font-size:24px}table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:12px;border-bottom:1px solid #ddd}.table{overflow:auto}.status{color:#14623c;font-weight:bold}</style></head>
<body><main>
<p>PT STH NETWORK · Sistem Stok Barang</p><h1>Verifikasi Persetujuan Pemesanan</h1>
<p class="status">Disetujui oleh pimpinan</p>
<p><strong>Nomor pengajuan:</strong> {{ $document->kode }}<br>
<strong>Tanggal pengajuan:</strong> {{ $document->tanggal->format('d/m/Y') }}<br>
<strong>Pemohon:</strong> {{ $document->pemohon }}<br>
<strong>Supplier:</strong> {{ $document->penerima }}<br>
<strong>Disetujui oleh:</strong> {{ $document->approver_name }} (Pimpinan)<br>
<strong>Tanggal ACC:</strong> {{ $document->approved_at->format('d/m/Y H:i') }}</p>
<div class="table"><table><thead><tr><th>Kode</th><th>Barang disetujui</th><th>Jumlah</th></tr></thead><tbody>
@foreach($document->details as $detail)<tr><td>{{ $detail->kode_barang }}</td><td>{{ $detail->nama_barang }}</td><td>{{ $detail->jumlah }} {{ $detail->satuan }}</td></tr>@endforeach
</tbody></table></div>
<p>Halaman ini merupakan bukti persetujuan yang tercatat dalam sistem. Persetujuan tidak berarti barang sudah diterima.</p>
</main></body></html>
