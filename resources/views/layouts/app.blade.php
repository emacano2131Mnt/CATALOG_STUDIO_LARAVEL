<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Catalog Studio')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/studio.css') }}">
    @stack('styles')
</head>
<body>
<div class="shell">
    {{-- Navegación lateral reutilizable --}}
    @include('components.navigation')

    <div class="main">
        <div class="topbar">
            <button class="menu-btn" type="button" aria-label="Abrir menú" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
            <span class="muted">@yield('title', 'Catalog Studio')</span>
            @auth
                <a href="{{ route('profile.edit') }}" class="actions" title="Mi perfil">
                    <span class="muted">{{ auth()->user()->name }}</span>
                    <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                </a>
            @endauth
        </div>

        <main class="content">
            @if (session('status')) <div class="alert ok" role="status"><i class="bi bi-check-circle"></i> {{ session('status') }}</div> @endif
            @if (session('error')) <div class="alert err" role="alert"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}</div> @endif
            @if ($errors->any())
                <div class="alert err" role="alert">
                    <strong>Revise los siguientes campos:</strong>
                    <ul style="margin:.3rem 0 0 1.1rem;padding:0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
