<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bedankt voor je inschrijving</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h1 class="mb-3">Bedankt voor je inschrijving!</h1>
                    <p class="lead mb-4">
                        Als je betaling is voltooid bij Mollie, wordt je inschrijving definitief verwerkt.
                    </p>
                    <p class="mb-0">
                        Je hebt ingeschreven als:
                    </p>
                    <p class="fw-bold mb-4">
                        {{ $registration->first_name }} {{ $registration->last_name }}
                    </p>
                    <a href="{{ route('inschrijven.create') }}" class="btn btn-primary">
                        Nog iemand inschrijven
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

