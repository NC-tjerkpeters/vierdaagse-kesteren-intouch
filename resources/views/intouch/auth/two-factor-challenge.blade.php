<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Twee-factor verificatie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
        .btn-vierdaagse { background-color: #2e7d32; color: #fff; border: none; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-success text-white">Twee-factor verificatie</div>
                <div class="card-body">
                    <p class="text-muted mb-3">Voer de code uit je authenticator-app in, of een herstelcode.</p>
                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form method="post" action="{{ route('intouch.login.two-factor.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">Verificatiecode (6 cijfers)</label>
                            <input type="text" id="code" name="code" class="form-control" inputmode="numeric" maxlength="6" placeholder="000000">
                        </div>
                        <div class="mb-3">
                            <label for="recovery_code" class="form-label">Of herstelcode</label>
                            <input type="text" id="recovery_code" name="recovery_code" class="form-control" placeholder="XXXX-XXXX">
                        </div>
                        <button type="submit" class="btn btn-vierdaagse">Verifiëren</button>
                        <a href="{{ route('intouch.login') }}" class="btn btn-link ms-2">Terug naar inloggen</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
