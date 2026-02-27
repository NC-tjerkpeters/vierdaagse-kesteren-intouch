<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scanner – Inloggen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --vk-green: #2e7d32; --vk-green-dark: #1b5e20; }
        .btn-vierdaagse { background: var(--vk-green); color: #fff; border: none; }
        .btn-vierdaagse:hover { background: var(--vk-green-dark); color: #fff; }
        .qr-promo { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%); color: #fff; border-radius: 12px; padding: 1.5rem; text-align: center; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h4 text-center mb-4">Vierdaagse Scanner</h1>

            <div class="qr-promo mb-4">
                <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                <h2 class="h5 mb-2">Inloggen met QR-code</h2>
                <p class="mb-0 small opacity-90">Open je camera en scan de QR-code die de organisator op het scherm of op papier toont. Geen wachtwoord nodig.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex align-items-center gap-2 my-3">
                <hr class="flex-grow-1">
                <span class="text-muted small">of met account</span>
                <hr class="flex-grow-1">
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" action="{{ route('scanner.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Wachtwoord</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input" value="1">
                            <label class="form-check-label" for="remember">Onthoud mij</label>
                        </div>
                        <button type="submit" class="btn btn-vierdaagse w-100">Inloggen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
