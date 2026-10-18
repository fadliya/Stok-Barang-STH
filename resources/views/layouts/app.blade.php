<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false, desktop: window.innerWidth >= 1024 }" :class="{ 'dark': darkMode }" @resize.window="desktop = window.innerWidth >= 1024">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - PT STH Network</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar for white theme */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #ff8000;
        }
    </style>
</head>
<body @keydown.escape.window="sidebarOpen = false" class="inventory-shell bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 font-sans antialiased min-h-screen transition-colors duration-200">

    <a class="skip-link" href="#main-content">Lewati navigasi</a><div class="flex h-dvh overflow-hidden">
        
        <!-- Sidebar Backdrop (Mobile) -->
        <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>

        <!-- Sidebar Left (Clean White Theme with #ff8000 Accent) -->
        <aside id="app-sidebar" :inert="!sidebarOpen && !desktop" x-trap.inert.noscroll="sidebarOpen && !desktop" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 transform lg:static lg:translate-x-0 shadow-sm"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <!-- Brand Header -->
            <div class="flex items-center justify-between h-20 px-6 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="p-2 rounded-2xl bg-orange-50 dark:bg-slate-800 border border-[#ff8000]/20 flex items-center justify-center">
                        <img src="{{ asset('assets/logo.png') }}" alt="PT STH Network Logo" class="h-9 w-auto object-contain">
                    </div>
                    <div>
                        <h1 class="font-extrabold text-sm tracking-wide text-slate-900 dark:text-white">STH NETWORK</h1>
                        <p class="text-[10px] text-[#ff8000] font-extrabold tracking-wider uppercase">Stok Barang System</p>
                    </div>
                </div>
                <button aria-label="Tutup navigasi" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- User Info Badge -->
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 bg-orange-50/50 dark:bg-slate-800/40">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('profile.edit') }}" aria-label="Buka profil saya" class="shrink-0"><img src="{{ route('profile.photo') }}" alt="Foto profil" class="w-10 h-10 rounded-full object-cover"></a>
                    <div class="truncate">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <span class="inline-block px-2.5 py-0.5 text-[10px] font-extrabold tracking-wider rounded-full uppercase bg-[#ff8000]/10 text-[#ff8000] border border-[#ff8000]/30">
                            <i class="fa-solid fa-shield-halved mr-1"></i>{{ auth()->user()->role }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-chart-pie w-6 text-center text-base mr-3"></i>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->isAdmin())
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Master Data</p>
                </div>

                <a href="{{ route('users.index') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-users w-6 text-center text-base mr-3"></i>
                    <span>Kelola User</span>
                </a>

                <a href="{{ route('suppliers.index') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('suppliers.*') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-truck-field w-6 text-center text-base mr-3"></i>
                    <span>Kelola Supplier</span>
                </a>

                <a href="{{ route('categories.index') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('categories.*') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-tags w-6 text-center text-base mr-3"></i>
                    <span>Kategori Barang</span>
                </a>
                @endif

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Inventaris Stok</p>
                </div>

                <a href="{{ route('items.index') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('items.*') && !request()->routeIs('items.print-barcodes') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-box-archive w-6 text-center text-base mr-3"></i>
                    <span>Data Barang</span>
                </a>

                <a href="{{ route('goods-requests.index') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all {{ request()->routeIs('goods-requests.*') ? 'bg-[#ff8000] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800' }}">
                    <i class="fa-solid fa-file-lines w-6 text-center text-base mr-3"></i><span>Pemesanan Supplier</span>
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isGudang())
                <a href="{{ route('pos.scan') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('pos.*') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-barcode w-6 text-center text-base mr-3 text-[#ff8000]"></i>
                    <span>Universal Scan POS</span>
                </a>

                <a href="{{ route('incoming.index') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('incoming.*') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-download w-6 text-center text-base mr-3 text-emerald-500"></i>
                    <span>Barang Masuk</span>
                </a>



                <a href="{{ route('stock-opname.index') }}" class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all {{ request()->routeIs('stock-opname.*') ? 'bg-[#ff8000] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800' }}"><i class="fa-solid fa-clipboard-check w-6 text-center text-base mr-3"></i><span>Stok Opname</span></a>
                <a href="{{ route('outgoing.index') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('outgoing.*') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-arrow-up-from-bracket w-6 text-center text-base mr-3 text-rose-500"></i>
                    <span>Barang Keluar</span>
                </a>

                <a href="{{ route('items.print-barcodes') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('items.print-barcodes') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-print w-6 text-center text-base mr-3 text-indigo-500"></i>
                    <span>Cetak Stiker Barcode</span>
                </a>
                @endif

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Laporan & Rekap</p>
                </div>

                <a href="{{ route('reports.incoming') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('reports.incoming') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-file-circle-check w-6 text-center text-base mr-3"></i>
                    <span>Laporan Masuk</span>
                </a>

                <a href="{{ route('reports.outgoing') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('reports.outgoing') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-file-circle-xmark w-6 text-center text-base mr-3"></i>
                    <span>Laporan Keluar</span>
                </a>

                <a href="{{ route('reports.stock') }}" 
                   class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-200 {{ request()->routeIs('reports.stock') ? 'bg-[#ff8000] text-white shadow-lg shadow-[#ff8000]/30' : 'text-slate-600 dark:text-slate-300 hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-[#ff8000]' }}">
                    <i class="fa-solid fa-warehouse w-6 text-center text-base mr-3"></i>
                    <span>Laporan Stok</span>
                </a>
            </nav>

            <!-- Bottom Action -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 mb-3 rounded-xl text-sm font-bold {{ request()->routeIs('profile.*') ? 'bg-orange-50 text-orange-700 dark:bg-slate-800' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"><i class="fa-solid fa-user-gear" aria-hidden="true"></i> Profil Saya</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2.5 text-sm font-bold text-rose-500 bg-rose-50 hover:bg-rose-100 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 rounded-2xl transition-colors border border-rose-200 dark:border-rose-500/20">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i>
                        <span>Keluar Sistem</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Top Navbar (Pure White) -->
            <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-3 sm:px-6 shadow-sm z-30 transition-colors">
                <div class="flex items-center space-x-2 sm:space-x-3 min-w-0 flex-1 mr-2">
                    <button aria-label="Buka navigasi" aria-controls="app-sidebar" :aria-expanded="sidebarOpen" @click="sidebarOpen = true" class="lg:hidden text-slate-600 dark:text-slate-300 hover:text-slate-900 focus:outline-none p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-lg font-extrabold text-slate-900 dark:text-white leading-tight truncate">@yield('title', 'Dashboard')</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block truncate">PT STH Network - Sistem Manajemen Stok Barang</p>
                    </div>
                </div>

                <div class="flex items-center space-x-1.5 sm:space-x-3 shrink-0">
                    <!-- Instagram Link Button -->
                    <a href="https://www.instagram.com/sthnetwork.id?igsh=MWJvcjQ4ejVxbTNscQ%3D%3D" target="_blank" rel="noopener noreferrer"
                       class="p-2 rounded-xl text-[#ff8000] hover:bg-orange-50 dark:hover:bg-slate-800 transition-colors flex items-center space-x-1.5 text-xs font-bold"
                       aria-label="Instagram PT STH Network" title="Instagram PT STH Network">
                        <i class="fa-brands fa-instagram text-lg"></i>
                        <span class="hidden md:inline">@sthnetwork.id</span>
                    </a>

                    <div class="h-5 w-px bg-slate-200 dark:bg-slate-800"></div>

                    <!-- Dark Mode Toggle Button -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                            class="p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition-colors"
                            aria-label="Ganti tema gelap atau terang" :aria-pressed="darkMode" title="Ganti tema">
                        <i class="fa-solid fa-moon text-lg" x-show="!darkMode"></i>
                        <i class="fa-solid fa-sun text-lg text-[#ff8000]" x-show="darkMode"></i>
                    </button>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800"></div>
                    @php
                        $lowStockAlerts = \App\Models\Item::whereColumn('stok', '<=', 'minimum_stok')->take(6)->get();
                        $lowStockAlertCount = \App\Models\Item::whereColumn('stok', '<=', 'minimum_stok')->count();
                    @endphp
                    <div class="relative" x-data="{ openNotification: false }">
                        <button @click="openNotification = !openNotification" 
                                class="relative p-2 rounded-xl text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none transition-colors" 
                                aria-label="Notifikasi stok" :aria-expanded="openNotification" title="Notifikasi Stok Tipis">
                            <i class="fa-solid fa-bell text-lg"></i>
                            @if($lowStockAlertCount > 0)
                            <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-extrabold text-white animate-pulse">
                                {{ $lowStockAlertCount }}
                            </span>
                            @endif
                        </button>

                        <div x-cloak x-show="openNotification" @click.outside="openNotification = false" 
                             class="fixed sm:absolute left-3 right-3 sm:left-auto sm:right-0 top-16 sm:top-full sm:mt-2 w-auto sm:w-80 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 p-4 space-y-3 z-50 transition-all">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                                <span class="font-extrabold text-xs text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-1.5">
                                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                    <span>Peringatan Stok Tipis</span>
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-extrabold bg-rose-500/10 text-rose-600 rounded-full">{{ $lowStockAlertCount }} Barang</span>
                            </div>

                            <div class="space-y-2 max-h-64 sm:max-h-72 overflow-y-auto pr-1">
                                @forelse($lowStockAlerts as $alertItem)
                                <a href="{{ route('items.show', $alertItem->id) }}" class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl sm:rounded-2xl flex items-center justify-between hover:bg-orange-50 dark:hover:bg-slate-800 transition text-xs block group">
                                    <div class="flex-1 min-w-0 mr-2">
                                        <p class="font-bold text-slate-900 dark:text-white truncate group-hover:text-[#ff8000]">{{ $alertItem->nama_barang }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $alertItem->kode_barang }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <span class="px-2 py-1 text-[10px] font-extrabold rounded-full bg-rose-500/10 text-rose-600 whitespace-nowrap">
                                            Sisa: {{ $alertItem->stok }} {{ $alertItem->satuan }}
                                        </span>
                                    </div>
                                </a>
                                @empty
                                <div class="text-center py-4 text-xs text-slate-400">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-lg mb-1 block"></i>
                                    <span>Semua stok barang aman!</span>
                                </div>
                                @endforelse
                            </div>
                            
                            @if($lowStockAlertCount > 0 && (auth()->user()->isAdmin() || auth()->user()->isGudang()))
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-center">
                                <a href="{{ route('pos.scan', ['type' => 'incoming']) }}" class="text-[11px] font-extrabold text-[#ff8000] hover:underline">
                                    + Tambah Stok via POS Scanner →
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800"></div>

                    <!-- User Quick Info -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-200 hidden md:block">
                            {{ auth()->user()->username }}
                        </span>
                        <span class="px-3 py-1 text-xs font-extrabold rounded-full uppercase bg-[#ff8000] text-white shadow-sm shadow-[#ff8000]/20">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Main Page Scrollable Body -->
            <main id="main-content" tabindex="-1" class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                
                <!-- Toast Alerts -->
                @if(session('receipt_url'))
                    <div class="mb-4 p-4 bg-orange-50 rounded-xl"><a class="font-bold text-orange-700 underline" href="{{ session('receipt_url') }}">Lihat / Cetak Struk Transaksi</a></div>
                @endif
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show"
                         class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-check text-xl text-emerald-500"></i>
                            <span class="text-sm font-semibold">{{ session('success') }}</span>
                        </div>
                        <button aria-label="Tutup pemberitahuan" @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" 
                         class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-exclamation text-xl text-rose-500"></i>
                            <span class="text-sm font-semibold">{{ session('error') }}</span>
                        </div>
                        <button aria-label="Tutup pemberitahuan" @click="show = false" class="text-rose-500 hover:text-rose-700">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         class="mb-6 p-4 rounded-2xl bg-orange-500/10 border border-orange-500/30 text-[#ff8000] flex items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-info text-xl text-[#ff8000]"></i>
                            <span class="text-sm font-semibold">{{ session('info') }}</span>
                        </div>
                        <button aria-label="Tutup pemberitahuan" @click="show = false" class="text-[#ff8000] hover:text-[#e67300]">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200" role="alert" tabindex="-1" id="validation-errors">
                        <h2 class="font-bold mb-2">Periksa kembali isian berikut</h2>
                        <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>

            <!-- Footer (Clean White) -->
            <footer class="py-4 px-6 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                <div>
                    &copy; {{ date('Y') }} <span class="font-extrabold text-slate-900 dark:text-white">PT STH Network</span>. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <a href="https://www.instagram.com/sthnetwork.id?igsh=MWJvcjQ4ejVxbTNscQ%3D%3D" target="_blank" class="text-[#ff8000] hover:underline flex items-center space-x-1 font-bold">
                    <i class="fa-brands fa-instagram text-sm"></i>
                    <span>@sthnetwork.id</span>
                </a>
            </footer>
        </div>
    </div>

    <!-- Page scripts -->
    @stack('scripts')
</body>
</html>
