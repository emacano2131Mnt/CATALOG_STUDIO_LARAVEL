@extends('layouts.app')
@section('title', 'Diseñador · '.$catalog->name)
@section('content')
@php
    $v = fn ($key) => old('design.'.$key, $design[$key]);
    $checked = fn ($key) => old('design') !== null ? (bool) old('design.'.$key) : (bool) $design[$key];
@endphp
<div class="page-head">
    <div><h1>Diseñador: {{ $catalog->name }}</h1><p class="muted">Configura el diseño; la vista previa se actualiza al cambiar cualquier opción.</p></div>
    <div class="actions">
        <a class="btn sec" href="{{ route('catalogs.show', $catalog) }}">Volver al catálogo</a>
        <a class="btn" target="_blank" href="{{ route('catalogs.pdf', $catalog) }}"><i class="bi bi-eye"></i> Ver PDF</a>
        <a class="btn ok" href="{{ route('catalogs.pdf', [$catalog, 'download' => 1]) }}"><i class="bi bi-download"></i> Descargar PDF</a>
    </div>
</div>

<div class="grid" style="grid-template-columns:minmax(300px,420px) 1fr;align-items:start">
    <form method="POST" action="{{ route('designer.update', $catalog) }}" id="design-form" class="card">
        @csrf @method('PUT')

        <h3>Plantilla</h3>
        <div class="field">
            <select name="design[template]" id="template">
                @foreach ($templates as $key => $t)<option value="{{ $key }}" @selected($v('template') === $key)>{{ $t['label'] }}</option>@endforeach
            </select>
            <div class="hint">Al elegir otra plantilla se cargan sus colores y medidas base (puedes ajustarlos después).</div>
        </div>

        <h3>Textos</h3>
        <div class="field"><label>Título</label><input type="text" name="design[title]" value="{{ $v('title') }}" maxlength="120"></div>
        <div class="field"><label>Subtítulo</label><input type="text" name="design[subtitle]" value="{{ $v('subtitle') }}" maxlength="200"></div>
        <div class="field"><label>Texto de portada</label><textarea name="design[cover_text]" rows="2" maxlength="500">{{ $v('cover_text') }}</textarea></div>
        <div class="field"><label>Encabezado</label><input type="text" name="design[header_text]" value="{{ $v('header_text') }}" maxlength="150"></div>
        <div class="field"><label>Pie de página</label><input type="text" name="design[footer_text]" value="{{ $v('footer_text') }}" maxlength="150"></div>

        <h3>Colores y tipografía</h3>
        <div class="grid g2">
            <div class="field"><label>Principal</label><input type="color" name="design[primary_color]" value="{{ $v('primary_color') }}"></div>
            <div class="field"><label>Secundario</label><input type="color" name="design[secondary_color]" value="{{ $v('secondary_color') }}"></div>
            <div class="field"><label>Texto</label><input type="color" name="design[text_color]" value="{{ $v('text_color') }}"></div>
            <div class="field"><label>Fondo</label><input type="color" name="design[background_color]" value="{{ $v('background_color') }}"></div>
        </div>
        <div class="field">
            <label>Tipografía</label>
            <select name="design[font]">@foreach ($fonts as $key => $f)<option value="{{ $key }}" @selected($v('font') === $key)>{{ $f['label'] }}</option>@endforeach</select>
        </div>

        <h3>Distribución</h3>
        <div class="grid g2">
            <div class="field"><label>Columnas</label><select name="design[columns]">@foreach ([1,2,3,4] as $n)<option value="{{ $n }}" @selected((int) $v('columns') === $n)>{{ $n }}</option>@endforeach</select></div>
            <div class="field"><label>Tamaño de foto</label><select name="design[photo_size]">@foreach (['small' => 'Pequeña', 'medium' => 'Mediana', 'large' => 'Grande'] as $k => $l)<option value="{{ $k }}" @selected($v('photo_size') === $k)>{{ $l }}</option>@endforeach</select></div>
            <div class="field"><label>Márgenes (mm)</label><input type="number" name="design[margin]" min="5" max="40" value="{{ $v('margin') }}"></div>
            <div class="field"><label>Espaciado (px)</label><input type="number" name="design[spacing]" min="0" max="40" value="{{ $v('spacing') }}"></div>
            <div class="field"><label>Moneda</label><input type="text" name="design[currency]" maxlength="5" value="{{ $v('currency') }}"></div>
        </div>

        <h3>Visibilidad</h3>
        <label class="check"><input type="checkbox" name="design[cover]" value="1" @checked($checked('cover'))> Mostrar portada</label>
        <label class="check"><input type="checkbox" name="design[show_prices]" value="1" @checked($checked('show_prices'))> Mostrar precios</label>
        <label class="check"><input type="checkbox" name="design[show_descriptions]" value="1" @checked($checked('show_descriptions'))> Mostrar descripciones</label>
        <label class="check"><input type="checkbox" name="design[show_categories]" value="1" @checked($checked('show_categories'))> Mostrar categorías</label>

        <div class="actions" style="margin-top:1rem"><button class="btn" type="submit"><i class="bi bi-save"></i> Guardar diseño</button></div>
    </form>

    <div class="card">
        <h2>Vista previa</h2>
        <iframe id="preview" title="Vista previa del catálogo" style="width:100%;height:760px;border:1px solid var(--line);border-radius:10px;background:#fff" src="{{ route('designer.preview', $catalog) }}"></iframe>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('design-form');
    const frame = document.getElementById('preview');
    const base = @json(route('designer.preview', $catalog));
    const templates = @json($templates);
    let timer;

    function refresh() {
        // El formulario completo se envía como query string; los checkboxes desmarcados no se envían (= false).
        const params = new URLSearchParams();
        new FormData(form).forEach((val, key) => { if (key.startsWith('design[')) params.append(key, val); });
        frame.src = base + '?' + params.toString() + (params.has('design[cover]') ? '' : '&design[cover]=0');
    }
    form.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(refresh, 350); });

    // Al cambiar de plantilla se aplican sus valores base a los campos.
    document.getElementById('template').addEventListener('change', (e) => {
        const t = templates[e.target.value];
        for (const [key, val] of Object.entries(t)) {
            const el = form.elements['design[' + key + ']'];
            if (!el || key === 'title' || key === 'subtitle') continue;
            if (el.type === 'checkbox') el.checked = !!val; else el.value = val;
        }
        refresh();
    });
</script>
@endpush
