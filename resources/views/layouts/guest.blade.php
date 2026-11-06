<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Catalog Studio')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/studio.css') }}">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <a href="{{ route('home') }}" class="brand" style="text-decoration:none"><i class="bi bi-grid-1x2-fill"></i> Catalog Studio</a>
        @if (session('status')) <div class="alert ok" role="status">{{ session('status') }}</div> @endif
        @yield('content')
    </div>
</div>
</body>
</html>
