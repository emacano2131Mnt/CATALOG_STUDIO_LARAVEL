@extends('layouts.app')
@section('title', 'Dashboard · Catalog Studio')
@section('content')
<div class="page-head">
    <div>
        <h1>Hola, {{ $user->name }} 👋</h1>
        <p class="muted">{{ $user->email }} · Miembro desde {{ $user->created_at?->translatedFormat('d \d\e F \d\e Y') }}</p>
    </div>
    <div class="actions">
        <a class="btn" href="{{ route('catalogs.create') }}"><i class="bi bi-plus-lg"></i> Nuevo catálogo</a>
        <a class="btn sec" href="{{ route('products.create') }}"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
    </div>
</div>

<div class="grid g3">
    <div class="card stat"><div class="ico"><i class="bi bi-journal-richtext"></i></div><div><b id="stat-catalogs">{{ $stats['catalogs'] }}</b><span class="muted">Mis catálogos</span></div></div>
    <div class="card stat ok"><div class="ico"><i class="bi bi-box-seam"></i></div><div><b id="stat-products">{{ $stats['products'] }}</b><span class="muted">Mis productos</span></div></div>
    <div class="card stat"><div class="ico"><i class="bi bi-tags"></i></div><div><b id="stat-categories">{{ $stats['categories'] }}</b><span class="muted">Categorías disponibles</span></div></div>
</div>

<div class="card">
    <h2>Productos por categoría</h2>
    @if (count($chart['values']))
        <div class="chart-box"><canvas id="categoryChart" aria-label="Distribución de productos por categoría"></canvas></div>
    @else
        <p class="empty">Aún no tienes productos para graficar. <a href="{{ route('products.create') }}">Crea tu primer producto</a>.</p>
    @endif
</div>

<div class="grid g2">
    <div class="card">
        <h2>Catálogos recientes</h2>
        <table class="tbl">
            <tbody>
            @forelse ($recentCatalogs as $catalog)
                <tr><td><a href="{{ route('catalogs.show', $catalog) }}">{{ $catalog->name }}</a></td><td><span class="badge">{{ $catalog->products_count }} productos</span></td><td class="muted">{{ $catalog->created_at->diffForHumans() }}</td></tr>
            @empty
                <tr><td class="empty">Sin catálogos todavía.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card">
        <h2>Productos recientes</h2>
        <table class="tbl">
            <tbody>
            @forelse ($recentProducts as $product)
                <tr>
                    <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a><br><span class="muted">{{ $product->catalog->name }}</span></td>
                    <td>{{ $product->category?->name ?? '—' }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                </tr>
            @empty
                <tr><td class="empty">Sin productos todavía.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@if (count($chart['values']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($chart);
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: chartData.labels,
            datasets: [{ data: chartData.values, backgroundColor: ['#6d28d9','#16a34a','#8b5cf6','#f59e0b','#0ea5e9','#ec4899','#14b8a6','#64748b'] }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });
</script>
@endif
@endpush
