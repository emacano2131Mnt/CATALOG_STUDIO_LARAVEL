<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catalog Studio · Crea catálogos profesionales</title>
    <meta name="description" content="Catalog Studio: crea, diseña y exporta catálogos de productos en PDF.">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/studio.css') }}">
</head>
<body style="background:#fff">
<header class="land-nav">
    <a href="{{ route('home') }}" class="brand" style="text-decoration:none;margin:0"><i class="bi bi-grid-1x2-fill"></i> Catalog Studio</a>
    <div class="actions">
        @auth
            <a class="btn" href="{{ route('dashboard') }}">Ir al dashboard</a>
        @else
            <a href="{{ route('login') }}">Iniciar sesión</a>
            <a class="btn" href="{{ route('register') }}">Registrarse</a>
        @endauth
    </div>
</header>

<section class="hero">
    <h1>Crea catálogos de productos que venden por sí solos</h1>
    <p>Organiza tus productos, personaliza el diseño y exporta catálogos en PDF listos para compartir, todo desde un solo lugar.</p>
    <div class="actions" style="justify-content:center">
        @auth
            <a class="btn" href="{{ route('dashboard') }}">Abrir mi panel</a>
        @else
            <a class="btn" href="{{ route('register') }}" style="background:#fff;color:var(--p-dark);border-color:#fff"><i class="bi bi-rocket-takeoff"></i> Comenzar gratis</a>
            <a class="btn sec" href="{{ route('login') }}">Ya tengo cuenta</a>
        @endauth
    </div>
</section>

<section class="land-section">
    <h2>Todo lo que necesitas para tu catálogo</h2>
    <div class="grid g3">
        <div class="card feature"><i class="bi bi-journal-richtext"></i><h3>Catálogos y productos</h3><p class="muted">Crea catálogos, agrega productos con fotografías, precios y categorías, y ordénalos a tu gusto.</p></div>
        <div class="card feature"><i class="bi bi-palette"></i><h3>Diseñador personalizado</h3><p class="muted">Cuatro plantillas (minimalista, premium, comercial y corporativa) con colores, tipografías, columnas y más.</p></div>
        <div class="card feature"><i class="bi bi-file-earmark-pdf"></i><h3>Exportación a PDF</h3><p class="muted">Descarga el catálogo completo o solo los productos que elijas, con tu diseño aplicado.</p></div>
        <div class="card feature"><i class="bi bi-bar-chart-line"></i><h3>Dashboard con estadísticas</h3><p class="muted">Visualiza tus catálogos, productos y la distribución por categoría en tiempo real.</p></div>
        <div class="card feature"><i class="bi bi-cloud-download"></i><h3>Importación externa</h3><p class="muted">Importa productos de ejemplo desde DummyJSON con un clic y sin duplicados.</p></div>
        <div class="card feature"><i class="bi bi-shield-lock"></i><h3>Privado y seguro</h3><p class="muted">Cada usuario ve y administra únicamente sus propios datos. API REST con tokens personales.</p></div>
    </div>
</section>

<section class="land-section" style="text-align:center">
    <h2>Empieza en tres pasos</h2>
    <div class="grid g3">
        <div><span class="badge ok">1</span><h3>Regístrate</h3><p class="muted">Crea tu cuenta en segundos.</p></div>
        <div><span class="badge ok">2</span><h3>Agrega productos</h3><p class="muted">Súbelos o impórtalos desde la API externa.</p></div>
        <div><span class="badge ok">3</span><h3>Exporta tu PDF</h3><p class="muted">Diseña y descarga tu catálogo.</p></div>
    </div>
</section>

<footer class="land-foot">Catalog Studio · Proyecto académico desarrollado con Laravel 12</footer>
</body>
</html>
