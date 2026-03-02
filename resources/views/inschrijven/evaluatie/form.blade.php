<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Evaluatie – Vierdaagse Kesteren</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4 text-center">Vierdaagse Kesteren – Evaluatie {{ $evaluation->edition->name }}</h1>

            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
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

            @if($evaluation->intro_text)
                <div class="mb-4 p-3 bg-white rounded">{{ nl2br(e($evaluation->intro_text)) }}</div>
            @endif

            <form method="post" action="{{ url()->full() }}">
                @csrf

                @foreach($evaluation->questions as $q)
                <div class="card mb-3">
                    <div class="card-body">
                        <label class="form-label fw-bold">{{ $q->question_text }}@if($q->is_required) * @endif</label>

                        @if($q->type === 'nps')
                            <div class="d-flex flex-wrap gap-1 mb-0">
                                @foreach(range(0, 10) as $i)
                                <div class="form-check form-check-inline">
                                    <input type="radio" name="q_{{ $q->id }}" id="q{{ $q->id }}_{{ $i }}" value="{{ $i }}" class="form-check-input"
                                        @checked(old('q_' . $q->id) === (string)$i) @required($q->is_required)>
                                    <label class="form-check-label small" for="q{{ $q->id }}_{{ $i }}">{{ $i }}</label>
                                </div>
                                @endforeach
                            </div>
                            <small class="text-muted d-block mt-1">0 = zeer onwaarschijnlijk, 10 = zeer waarschijnlijk</small>
                        @elseif($q->type === 'rating')
                            <div class="d-flex gap-2">
                                @foreach(range(1, 5) as $i)
                                <div class="form-check">
                                    <input type="radio" name="q_{{ $q->id }}" id="q{{ $q->id }}_{{ $i }}" value="{{ $i }}" class="form-check-input"
                                        @checked(old('q_' . $q->id) === (string)$i) @required($q->is_required)>
                                    <label class="form-check-label" for="q{{ $q->id }}_{{ $i }}">★{{ $i }}</label>
                                </div>
                                @endforeach
                            </div>
                        @elseif($q->type === 'choice')
                            <div>
                                @foreach($q->options ?? [] as $opt)
                                <div class="form-check">
                                    <input type="radio" name="q_{{ $q->id }}" id="q{{ $q->id }}_{{ Str::slug($opt) }}" value="{{ $opt }}" class="form-check-input"
                                        @required($q->is_required)>
                                    <label class="form-check-label" for="q{{ $q->id }}_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <textarea name="q_{{ $q->id }}" class="form-control" rows="3"
                                @required($q->is_required)>{{ old('q_' . $q->id) }}</textarea>
                        @endif
                    </div>
                </div>
                @endforeach

                <button type="submit" class="btn btn-primary btn-lg w-100">Versturen</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
