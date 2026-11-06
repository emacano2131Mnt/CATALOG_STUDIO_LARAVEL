<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Catalog Studio'))</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:1100px;margin:2rem auto;padding:0 1rem;color:#20242b}nav{display:flex;gap:1rem;border-bottom:1px solid #ddd;padding:1rem 0}a{color:#2457a7}main{padding:1.5rem 0}.notice{padding:.75rem;background:#e9f7ee}
    </style>
</head>
<body>
    <header><a href="{{ route('dashboard') }}"><strong>Catalog Studio</strong></a>
        @include('components.navigation')
    </header>
    <main>
        @if (session('status')) <p class="notice" role="status">{{ session('status') }}</p> @endif
        @yield('content')
    </main>
</body>
</html>
