<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bedankt – Vierdaagse Kesteren</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="display-4 text-success mb-3">OK</div>
            <h1 class="mb-4">Bedankt!</h1>
            <p class="lead">{{ $thankYouText ? nl2br(e($thankYouText)) : 'Bedankt voor je feedback! We nemen je suggesties mee bij de voorbereiding van volgend jaar.' }}</p>
        </div>
    </div>
</div>
</body>
</html>
