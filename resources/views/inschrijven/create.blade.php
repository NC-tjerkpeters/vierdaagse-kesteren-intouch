@php use Illuminate\Support\Facades\Session; @endphp

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inschrijven Vierdaagse Kesteren</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4 text-center">Inschrijven Vierdaagse Kesteren</h1>

            @if(Session::has('status'))
                <div class="alert alert-success">
                    {{ Session::get('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" action="{{ route('inschrijven.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Voornaam</label>
                                <input type="text" id="first_name" name="first_name"
                                       value="{{ old('first_name') }}"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Achternaam</label>
                                <input type="text" id="last_name" name="last_name"
                                       value="{{ old('last_name') }}"
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Postcode</label>
                                <input type="text" id="postal_code" name="postal_code"
                                       value="{{ old('postal_code') }}"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="house_number" class="form-label">Huisnummer</label>
                                <input type="text" id="house_number" name="house_number"
                                       value="{{ old('house_number') }}"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="phone_number" class="form-label">Telefoonnummer</label>
                                <input type="text" id="phone_number" name="phone_number"
                                       value="{{ old('phone_number') }}"
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email') }}"
                                   class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="distance_id" class="form-label">Welke afstand ga je lopen?</label>
                            <select id="distance_id" name="distance_id" class="form-select" required>
                                <option value="">Maak een keuze</option>
                                @foreach($distances as $distance)
                                    <option value="{{ $distance->id }}" @selected(old('distance_id') == $distance->id)>
                                        {{ $distance->name }} ({{ number_format($distance->price, 2, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="wants_medal" name="wants_medal"
                                   value="1" @checked(old('wants_medal'))>
                            <label class="form-check-label" for="wants_medal">Ik ontvang graag een medaille</label>
                        </div>

                        <div class="mb-3" id="medal_number_group" style="display: none;">
                            <label for="medal_number" class="form-label">Medaillenummer (loopt voor medaillenummer)</label>
                            <input type="number" id="medal_number" name="medal_number"
                                   value="{{ old('medal_number') }}"
                                   class="form-control" min="1">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Inschrijven
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const wantsMedalCheckbox = document.getElementById('wants_medal');
    const medalGroup = document.getElementById('medal_number_group');

    function toggleMedal() {
        if (wantsMedalCheckbox.checked) {
            medalGroup.style.display = 'block';
        } else {
            medalGroup.style.display = 'none';
        }
    }

    wantsMedalCheckbox.addEventListener('change', toggleMedal);
    toggleMedal();
</script>
</body>
</html>

