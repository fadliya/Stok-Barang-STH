@extends('layouts.app')
@section('title', 'Ringkasan Inventaris')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-end gap-4">
        <div><p class="eyebrow">PUSAT OPERASIONAL · {{ now()->locale('id')->translatedFormat('l, d F Y') }}</p><h1 class="text-2xl sm:text-3xl font-bold">Inventaris dalam kendali.</h1><p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Selamat datang, {{ auth()->user()->name }}. Pantau persediaan dan aktivitas gudang hari ini.</p></div>
        @if(auth()->user()->isAdmin() || auth()->user()->isGudang())
        <div class="flex flex-wrap gap-2"><a class="action-secondary" href="{{ route('incoming.create') }}"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i>Barang masuk</a><a class="action-primary" href="{{ route('outgoing.create') }}"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i>Barang keluar</a></div>
        @endif
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([
            ['Barang terdaftar', $totalItems, 'jenis barang dalam inventaris', 'fa-boxes-stacked', 'text-slate-900 dark:text-white'],
            ['Perlu pengadaan', $lowStockItems->count(), 'stok mencapai batas minimum', 'fa-triangle-exclamation', 'text-rose-700 dark:text-rose-400'],
            ['Masuk hari ini', $incomingToday, 'total unit barang diterima', 'fa-arrow-down', 'text-emerald-700 dark:text-emerald-400'],
            ['Keluar hari ini', $outgoingToday, 'total unit barang dikeluarkan', 'fa-arrow-up', 'text-slate-900 dark:text-white'],
        ] as [$label, $value, $description, $icon, $color])
        <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl"><div class="flex justify-between items-center"><p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $label }}</p><i class="fa-solid {{ $icon }} text-slate-400" aria-hidden="true"></i></div><p class="text-3xl font-bold my-3 {{ $color }}">{{ number_format($value, 0, ',', '.') }}</p><p class="text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p></div>
        @endforeach
    </div>
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden" aria-labelledby="critical-stock">
        <div class="p-5 flex flex-wrap justify-between items-center gap-3 border-b border-slate-200 dark:border-slate-800"><div><h2 id="critical-stock" class="text-lg font-bold">Prioritas persediaan</h2><p class="text-sm text-slate-500 dark:text-slate-400">Barang dengan stok terendah untuk ditindaklanjuti.</p></div><a class="action-secondary" href="{{ route('items.index', ['stock' => 'low']) }}">Lihat semua · {{ $lowStockItems->count() }}</a></div>
        <div class="overflow-x-auto"><table class="w-full text-left"><thead class="bg-slate-50 dark:bg-slate-800"><tr><th scope="col" class="px-5 py-3">Barang</th><th scope="col" class="px-5 py-3">Supplier</th><th scope="col" class="px-5 py-3 text-right">Stok tersedia</th><th scope="col" class="px-5 py-3 text-right">Minimum</th><th scope="col" class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($lowStockItems->take(5) as $item)
            <tr><td class="px-5 py-4"><a class="font-semibold hover:underline" href="{{ route('items.show', $item->id) }}">{{ $item->nama_barang }}</a><p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->kode_barang }}</p></td><td class="px-5 py-4">{{ $item->supplier->nama ?? '-' }}</td><td class="px-5 py-4 text-right font-semibold">{{ $item->stok }} {{ $item->satuan }}</td><td class="px-5 py-4 text-right text-slate-500 dark:text-slate-400">{{ $item->minimum_stok }} {{ $item->satuan }}</td><td class="px-5 py-4"><span class="text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 rounded-full px-3 py-1 whitespace-nowrap">{{ $item->stok === 0 ? 'Stok habis' : 'Perlu pengadaan' }}</span></td></tr>
        @empty
            <tr><td colspan="5" class="p-8 text-center"><i class="fa-solid fa-circle-check text-emerald-600 mr-2" aria-hidden="true"></i>Semua persediaan berada di atas batas minimum.</td></tr>
        @endforelse
        </tbody></table></div>
    </section>
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden" aria-labelledby="recent-activity">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800"><h2 id="recent-activity" class="text-lg font-bold">Aktivitas terbaru</h2><p class="text-sm text-slate-500 dark:text-slate-400">Penerimaan dan pengeluaran terakhir beserta bukti transaksinya.</p></div>
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($recentTransactions as $entry)
            @php($tx = $entry['transaction'])
            <div class="p-5 flex flex-wrap items-center gap-4"><span class="w-10 h-10 flex items-center justify-center rounded-lg {{ $entry['type'] === 'incoming' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-orange-50 text-orange-700 dark:bg-orange-950 dark:text-orange-300' }}"><i class="fa-solid {{ $entry['type'] === 'incoming' ? 'fa-arrow-down' : 'fa-arrow-up' }}" aria-hidden="true"></i></span><div class="flex-1 min-w-0"><p class="font-semibold break-all">{{ $tx->kode_transaksi }}</p><p class="text-xs text-slate-500 dark:text-slate-400">{{ $entry['type'] === 'incoming' ? 'Barang masuk' : 'Barang keluar' }} · {{ $tx->user->name ?? '-' }} · {{ $tx->tanggal->format('d/m/Y H:i') }}</p></div><span class="text-sm">{{ $tx->details->count() }} jenis barang</span>@if(auth()->user()->isAdmin() || auth()->user()->isGudang())<a class="action-secondary" href="{{ route($entry['type'].'.receipt', $tx->id) }}">Lihat struk</a>@endif</div>
        @empty
            <p class="p-8 text-center text-slate-500 dark:text-slate-400">Belum ada transaksi. Catat penerimaan barang untuk mulai memantau aktivitas.</p>
        @endforelse
        </div>
    </section>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl"><h2 class="font-bold text-lg">Tren penerimaan</h2><p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Jumlah unit masuk · 6 bulan terakhir</p><div class="h-60"><canvas id="incomingChartCanvas" role="img" aria-label="Grafik penerimaan enam bulan terakhir"></canvas></div><p class="sr-only">@foreach($incomingChart['labels'] as $index => $label){{ $label }}: {{ $incomingChart['data'][$index] }} unit. @endforeach</p></section>
        <section class="p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl"><h2 class="font-bold text-lg">Tren pengeluaran</h2><p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Jumlah unit keluar · 6 bulan terakhir</p><div class="h-60"><canvas id="outgoingChartCanvas" role="img" aria-label="Grafik pengeluaran enam bulan terakhir"></canvas></div><p class="sr-only">@foreach($outgoingChart['labels'] as $index => $label){{ $label }}: {{ $outgoingChart['data'][$index] }} unit. @endforeach</p></section>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('app:ready', () => {
    const charts = [
        ['incomingChartCanvas', @js($incomingChart), '#047857', 'Barang masuk'],
        ['outgoingChartCanvas', @js($outgoingChart), '#c4510b', 'Barang keluar'],
    ];
    charts.forEach(([id, data, color, label]) => new Chart(document.getElementById(id), {
        type: 'bar', data: { labels: data.labels, datasets: [{ label, data: data.data, backgroundColor: color, borderRadius: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } }
    }));
});
</script>
@endpush
