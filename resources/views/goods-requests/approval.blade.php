<div class="p-4 border rounded-xl space-y-2">
    <p><strong>Status:</strong> {{ ['pending' => 'Menunggu ACC pimpinan', 'approved' => 'Disetujui', 'received' => 'Barang diterima'][$document->status] }}</p>
    <p><strong>Mengajukan:</strong> {{ $document->pemohon }} — tercatat oleh sistem pada {{ $document->created_at->format('d/m/Y H:i') }}</p>
    @if($document->approved_at)
        <p><strong>Mengetahui dan menyetujui:</strong> {{ $document->approver_name }} (Pimpinan) — ACC sistem pada {{ $document->approved_at->format('d/m/Y H:i') }}</p>
        <p>Bukti persetujuan sistem: {{ $document->kode }}. Tidak memerlukan tanda tangan manual.</p>
    @endif
    @if($document->received_at)<p>Diterima pada {{ $document->received_at->format('d/m/Y H:i') }}.</p>@endif
</div>
