@php
    $items = [
        ['dashboard', 'Dashboard', 'bi-speedometer2', 'dashboard'],
        ['catalogs.index', 'Catálogos', 'bi-journal-richtext', 'catalogs.*'],
        ['products.index', 'Productos', 'bi-box-seam', 'products.*'],
        ['categories.index', 'Categorías', 'bi-tags', 'categories.*'],
        ['designer.index', 'Diseñador', 'bi-palette', 'designer.*'],
        ['import.index', 'Importación', 'bi-cloud-download', 'import.*'],
        ['reports.index', 'Informes', 'bi-file-earmark-pdf', 'reports.*'],
        ['api.consumer', 'Consumo API', 'bi-plug', 'api.consumer'],
        ['profile.edit', 'Perfil', 'bi-person-circle', 'profile.*'],
    ];
@endphp
<aside class="sidebar" id="sidebar">
    <a href="{{ route('home') }}" class="brand" style="text-decoration:none"><i class="bi bi-grid-1x2-fill"></i> Catalog Studio</a>
    <nav aria-label="Navegación principal">
        @foreach ($items as [$route, $label, $icon, $pattern])
            <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}"><i class="bi {{ $icon }}"></i> {{ $label }}</a>
        @endforeach
    </nav>
    <div class="spacer"></div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
    </form>
</aside>
