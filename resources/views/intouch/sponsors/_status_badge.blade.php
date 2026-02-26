@php
    $map = [
        'paid' => 'success',
        'open' => 'warning',
        'pending' => 'warning',
        'authorized' => 'info',
        'failed' => 'danger',
        'canceled' => 'secondary',
        'expired' => 'secondary',
    ];
    $cls = $map[$status ?? ''] ?? 'dark';
@endphp
<span class="badge bg-{{ $cls }}">{{ $status ?: 'onbekend' }}</span>
