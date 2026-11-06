@extends('layouts.app')
@section('title', 'Consumo de la API propia · Catalog Studio')
@section('content')
<div class="page-head"><div><h1>Consumo de la API propia</h1><p class="muted">Esta página llama por HTTP (<code>fetch</code>) a los endpoints REST de Catalog Studio y muestra la respuesta real.</p></div></div>

<div class="card">
    <div class="filters">
        <div>
            <label for="endpoint">Endpoint</label>
            <select id="endpoint">
                <option value="/api/catalogs?per_page=5">GET /api/catalogs</option>
                <option value="/api/products?per_page=5">GET /api/products</option>
                <option value="/api/categories?per_page=5">GET /api/categories</option>
            </select>
        </div>
        <div style="flex:0"><button class="btn" id="run" type="button"><i class="bi bi-play-fill"></i> Consultar</button></div>
    </div>
    <p>Estado: <span class="badge" id="status">sin consultar</span> <span class="muted" id="url"></span></p>
    <div id="result"><p class="empty">Pulsa "Consultar".</p></div>
    <details><summary class="muted">Ver JSON crudo</summary><pre class="code" id="raw">{}</pre></details>
</div>
@endsection

@push('scripts')
<script>
    const $ = (id) => document.getElementById(id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    $('run').addEventListener('click', async () => {
        const url = $('endpoint').value;
        $('url').textContent = location.origin + url;
        $('status').textContent = 'consultando…';
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const json = await res.json();
            $('status').textContent = 'HTTP ' + res.status;
            $('status').className = 'badge' + (res.ok ? ' ok' : '');
            $('raw').textContent = JSON.stringify(json, null, 2);
            if (!res.ok) { $('result').innerHTML = '<div class="alert err">' + esc(json.message || 'Error') + '</div>'; return; }
            const rows = json.data || [];
            if (!rows.length) { $('result').innerHTML = '<p class="empty">La API respondió sin registros.</p>'; return; }
            const cols = Object.keys(rows[0]).filter(k => typeof rows[0][k] !== 'object' || rows[0][k] === null).slice(0, 6);
            $('result').innerHTML = '<table class="tbl"><thead><tr>' + cols.map(c => '<th>' + esc(c) + '</th>').join('') + '</tr></thead><tbody>'
                + rows.map(r => '<tr>' + cols.map(c => '<td>' + esc(r[c]) + '</td>').join('') + '</tr>').join('')
                + '</tbody></table><p class="muted">Total en la API: ' + esc(json.meta?.total ?? rows.length) + '</p>';
        } catch (e) {
            $('status').textContent = 'error de red';
            $('result').innerHTML = '<div class="alert err">No se pudo contactar la API: ' + esc(e.message) + '</div>';
        }
    });
</script>
@endpush
