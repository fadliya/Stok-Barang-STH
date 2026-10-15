<!DOCTYPE html>
<html lang="id" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - PT STH Network</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="inventory-shell bg-slate-100 dark:bg-slate-950 font-sans antialiased min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- White & Orange Ambient Glows -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#ff8000]/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-[#ff8000]/10 rounded-full blur-3xl pointer-events-none"></div>

    <main class="w-full max-w-md p-6 relative z-10">
        @yield('content')
    </main>

</body>
</html>
