<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Stiker Barcode Massal - PT STH Network</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .page-break { page-break-after: always; }
        }
        .barcode-card {
            width: 70mm;
            height: 38mm;
            page-break-inside: avoid;
        }
    </style>
</head>
<body class="inventory-shell bg-slate-100 min-h-screen p-6 font-sans">

    <!-- Header Action Bar (Hidden on Print) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Cetak Label Stiker Barcode Massal</h1>
            <p class="text-xs text-slate-500">Pratinjau label stiker barcode siap cetak. Pasang kertas stiker A4 atau Thermal Printer.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-[#ff8000] hover:bg-[#e67300] text-white text-xs font-extrabold rounded-xl shadow-md flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>CETAK BARCODE SEKARANG (Ctrl + P)</span>
            </button>
            <a href="{{ route('items.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">
                Kembali
            </a>
        </div>
    </div>

    <!-- Print Grid Container -->
    <div class="max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 justify-items-center">
            @foreach($items as $item)
                @php
                    $copies = request('copies', 1);
                @endphp
                @for($i = 0; $i < $copies; $i++)
                <div class="barcode-card border border-slate-300 rounded-lg p-2 flex flex-col items-center justify-between text-center bg-white">
                    <p class="font-extrabold text-[10px] text-slate-900 truncate w-full uppercase tracking-tight">{{ $item->nama_barang }}</p>
                    <div class="my-0.5">
                        <svg class="barcode-svg" 
                             data-value="{{ $item->barcode ?? $item->kode_barang }}"
                             style="max-width: 100%; height: 32px;"></svg>
                    </div>
                    <div class="w-full flex items-center justify-between text-[9px] font-bold text-slate-600 border-t border-slate-100 pt-0.5 px-1">
                        <span>{{ $item->kode_barang }}</span>
                        <span>{{ $item->category->nama ?? '-' }}</span>
                    </div>
                </div>
                @endfor
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener("app:ready", function() {
            document.querySelectorAll(".barcode-svg").forEach(function(el) {
                const val = el.getAttribute("data-value");
                if (val) {
                    JsBarcode(el, val, {
                        format: "CODE128",
                        width: 1.5,
                        height: 30,
                        displayValue: true,
                        fontSize: 9,
                        margin: 0
                    });
                }
            });
        });
    </script>
</body>
</html>
