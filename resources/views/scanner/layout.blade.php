<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Scanner') – Vierdaagse Kesteren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { margin: 0; background: #000; color: #fff; font-family: system-ui, -apple-system, sans-serif; text-align: center; }
        .scanner-header { display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap; padding: 12px; }
        .scanner-header h1 { font-size: 1.125rem; margin: 0; }
        .scanner-header .btn { padding: 6px 12px; border-radius: 6px; border: 1px solid #444; background: #222; color: #fff; }
        #qr-reader { width: 100%; max-width: 720px; margin: 0 auto; }
        .swal2-container { z-index: 2147483647 !important; }
        .swal2-popup { background: #1a1a1a !important; color: #fff !important; }
        .swal2-title, .swal2-html-container { color: #fff !important; }
        .swal2-confirm { border: none !important; }
        .scanner-overview { padding: 8px 12px; background: #111; border-bottom: 1px solid #333; font-size: 0.8rem; }
        .scanner-overview .overview-row { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
        .scanner-overview .overview-item { display: flex; align-items: center; gap: 4px; }
        .scanner-overview .overview-item span { color: #888; }
        .scanner-overview .overview-item strong { color: #fff; }
        .scanner-overview .overview-totals { margin-top: 6px; padding-top: 6px; border-top: 1px solid #333; }
    </style>
</head>
<body>
    <header class="scanner-header">
        <h1>@yield('header_title', 'QR Scanner')</h1>
        <button type="button" class="btn" id="btn-torch">Lampje aan</button>
        <form method="post" action="{{ route('scanner.logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn">Uitloggen</button>
        </form>
    </header>
    @hasSection('overview')
    <div class="scanner-overview">
        @yield('overview')
    </div>
    @endif
    <main>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-2">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show m-2">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
