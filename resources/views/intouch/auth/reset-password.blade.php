<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wachtwoord herstellen – Intouch Vierdaagse Kesteren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --vk-green: #2e7d32; --vk-green-dark: #1b5e20; }
        body { background-color: #f8f9fa; }
        .navbar-vierdaagse { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%) !important; }
        .card { border: none; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
        .card-header { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%); color: #fff; border-radius: 10px 10px 0 0; font-weight: 600; }
        .btn-vierdaagse { background-color: var(--vk-green); color: #fff; border: none; }
        .btn-vierdaagse:hover { background-color: var(--vk-green-dark); color: #fff; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark navbar-vierdaagse">
    <div class="container">
        <span class="navbar-brand mb-0 fw-bold">Intouch – Vierdaagse Kesteren</span>
    </div>
</nav>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Nieuw wachtwoord instellen</div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="post" action="{{ route('intouch.password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nieuw wachtwoord</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Wachtwoord bevestigen</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-vierdaagse">Wachtwoord wijzigen</button>
                    </form>

                    <p class="mt-3 mb-0">
                        <a href="{{ route('intouch.login') }}">← Terug naar inloggen</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
