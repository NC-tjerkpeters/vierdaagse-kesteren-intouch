<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Intouch') – Vierdaagse Kesteren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --vk-green: #2e7d32;
            --vk-green-dark: #1b5e20;
            --vk-green-light: #4caf50;
            --vk-cream: #f5f5dc;
        }
        body { background-color: #f8f9fa; }
        .navbar-vierdaagse {
            background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            z-index: 1030;
        }
        .navbar-vierdaagse .navbar-brand,
        .navbar-vierdaagse .nav-link { color: rgba(255,255,255,0.95) !important; }
        .navbar-vierdaagse .nav-link:hover { color: #fff !important; background-color: rgba(255,255,255,0.1); border-radius: 6px; }
        .navbar-vierdaagse .dropdown-menu { border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; }
        .navbar-vierdaagse .dropdown-item:hover { background-color: rgba(46, 125, 50, 0.12); }
        .navbar-vierdaagse .dropdown-divider { border-color: #e0e0e0; }
        #edition-select-form select { cursor: pointer; }
        #edition-select-form select option { color: #333; }
        .btn-vierdaagse { background-color: var(--vk-green); color: #fff; border: none; }
        .btn-vierdaagse:hover { background-color: var(--vk-green-dark); color: #fff; }
        .card { border-radius: 10px; border: none; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
        .card-header { background: linear-gradient(135deg, var(--vk-green) 0%, var(--vk-green-dark) 100%); color: #fff; border-radius: 10px 10px 0 0; font-weight: 600; }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-vierdaagse sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('intouch.dashboard') }}">Intouch – Vierdaagse Kesteren</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                @can('dashboard_view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('intouch.dashboard') }}">Dashboard</a>
                </li>
                @endcan
                @can('sponsors_view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('intouch.sponsors.index') }}">Sponsors</a>
                </li>
                @endcan
                @can('inschrijvingen_view')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Inschrijvingen</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('intouch.registrations.index') }}">Overzicht</a></li>
                        @can('inschrijvingen_medal_overview')
                        <li><a class="dropdown-item" href="{{ route('intouch.registrations.medal-overview') }}">Medaille-bestelling</a></li>
                        @endcan
                        @can('inschrijvingen_export')
                        <li><a class="dropdown-item" href="{{ route('intouch.registrations.export') }}">Exporteren</a></li>
                        @endcan
                        @can('communicatie_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.registrations.communicatie') }}">Communicatie</a></li>
                        @endcan
                        @can('evaluatie_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.registrations.evaluatie.index') }}">Evaluatie</a></li>
                        @endcan
                    </ul>
                </li>
                @endcan
                @can('loopoverzicht_view')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('intouch.scan-overview.index') }}">Loopoverzicht</a>
                </li>
                @endcan
                @if(auth()->user()->hasPermission('finances_view') || auth()->user()->hasPermission('editions_manage') || auth()->user()->hasPermission('routes_view') || auth()->user()->hasPermission('checklist_view') || auth()->user()->hasPermission('vrijwilligers_view'))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Werkgroep</a>
                    <ul class="dropdown-menu">
                        @can('finances_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.finance.index') }}">Financiën</a></li>
                        @endcan
                        @can('routes_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.walk-routes.index') }}">Routes</a></li>
                        <li><a class="dropdown-item" href="{{ route('intouch.route-templates.index') }}">Routebibliotheek</a></li>
                        @endcan
                        @can('checklist_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.werkgroep.checklist') }}">Checklist</a></li>
                        @endcan
                        @can('vrijwilligers_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.volunteers.index') }}">Vrijwilligersrooster</a></li>
                        @endcan
                    </ul>
                </li>
                @endif
                @if(auth()->user()->hasPermission('manage_users') || auth()->user()->hasPermission('manage_roles') || auth()->user()->hasPermission('editions_manage') || auth()->user()->hasPermission('afstanden_view') || auth()->user()->hasPermission('instellingen_edit'))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Beheer</a>
                    <ul class="dropdown-menu">
                        @can('afstanden_view')
                        <li><a class="dropdown-item" href="{{ route('intouch.beheer.afstanden.index') }}">Afstanden</a></li>
                        @endcan
                        @can('manage_users')
                        <li><a class="dropdown-item" href="{{ route('intouch.beheer.users.index') }}">Gebruikers</a></li>
                        @endcan
                        @can('manage_roles')
                        <li><a class="dropdown-item" href="{{ route('intouch.beheer.roles.index') }}">Rollen</a></li>
                        @endcan
                        @can('editions_manage')
                        <li><a class="dropdown-item" href="{{ route('intouch.beheer.editions.index') }}">Edities</a></li>
                        @endcan
                        @can('instellingen_edit')
                        <li><a class="dropdown-item" href="{{ route('intouch.beheer.instellingen.edit') }}">Instellingen</a></li>
                        @endcan
                        @can('manage_users')
                        <li><a class="dropdown-item" href="{{ route('intouch.beheer.status') }}">Status</a></li>
                        @endcan
                    </ul>
                </li>
                @endif
            </ul>
            <ul class="navbar-nav align-items-center">
                @can('dashboard_view')
                @if(isset($currentEdition) && $currentEdition)
                <li class="nav-item">
                    <form method="post" action="{{ route('intouch.edition.set') }}" class="d-flex align-items-center gap-1" id="edition-select-form">
                        @csrf
                        <span class="nav-link py-0 d-flex align-items-center">
                            <span class="text-white-50 me-1 small">Editie:</span>
                            <select name="edition_id" class="form-select form-select-sm border-0 bg-transparent text-white" style="width: auto; font-size: 0.9rem" onchange="this.form.submit()">
                                @php $activeEdition = \App\Models\Edition::active(); @endphp
                                <option value="" @selected(!session('edition_id'))>Actieve editie</option>
                                @foreach($editionsForSelector ?? [] as $e)
                                    <option value="{{ $e->id }}" @selected(session('edition_id') == $e->id)>{{ $e->name }}{{ $e->is_active ? ' (actief)' : '' }}</option>
                                @endforeach
                            </select>
                        </span>
                    </form>
                </li>
                @endif
                @endcan
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-1">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('intouch.instellingen.edit') }}">Mijn profiel</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="{{ route('intouch.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Uitloggen</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
