<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Routes') – Vierdaagse Kesteren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --vk-green: #2e7d32; --vk-green-dark: #1b5e20; }
        body { background-color: #f8f9fa; }
        .navbar-routes { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%); }
        .navbar-routes .navbar-brand, .navbar-routes .nav-link { color: rgba(255,255,255,0.95) !important; }
        .btn-vierdaagse { background-color: var(--vk-green); color: #fff; border: none; }
        .btn-vierdaagse:hover { background-color: var(--vk-green-dark); color: #fff; }
        .point-checked { text-decoration: line-through; color: #6c757d; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-routes">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('routes.index') }}">Routes – Vierdaagse Kesteren</a>
        <a class="nav-link" href="{{ route('routes.index') }}">Overzicht</a>
    </div>
</nav>
<main class="container py-4">@yield('content')</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
