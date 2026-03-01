<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Fout bij aanmelden – Vierdaagse Kesteren</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <h1 class="mb-3">Er ging iets mis</h1>
                    <p class="lead">{{ $message ?? 'Controleer of u alle velden correct hebt ingevuld en akkoord bent gegaan met de privacyverklaring.' }}</p>
                    <p class="mb-0">
                        <a href="{{ config('sponsors.form_url') }}" class="btn btn-primary">Terug naar het formulier</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
