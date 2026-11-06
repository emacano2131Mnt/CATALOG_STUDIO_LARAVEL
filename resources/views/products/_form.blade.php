@csrf
<div class="grid g2">
    <div class="field">
        <label for="catalog_id">Catálogo *</label>
        <select id="catalog_id" name="catalog_id" required>
            <option value="">Seleccione…</option>
            @foreach ($catalogs as $c)<option value="{{ $c->id }}" @selected(old('catalog_id', $product->catalog_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </div>
    <div class="field">
        <label for="category_id">Categoría</label>
        <select id="category_id" name="category_id">
            <option value="">Sin categoría</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <div class="hint"><a href="{{ route('categories.create') }}">Crear categoría</a></div>
    </div>
</div>
<div class="field"><label for="name">Nombre *</label><input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required maxlength="140"></div>
<div class="field"><label for="description">Descripción</label><textarea id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea></div>
<div class="grid g2">
    <div class="field"><label for="price">Precio *</label><input id="price" type="number" name="price" step="0.01" min="0" value="{{ old('price', $product->price) }}" required></div>
    <div class="field"><label for="position">Posición (orden manual)</label><input id="position" type="number" name="position" min="0" value="{{ old('position', $product->exists ? $product->position : '') }}"><div class="hint">Vacío = al final del catálogo.</div></div>
</div>
<div class="field">
    <label for="photo">Fotografía</label>
    @if ($product->exists && $product->image_src)
        <p><img class="thumb-lg" src="{{ $product->image_src }}" alt="Fotografía actual"></p>
        <label class="check"><input type="checkbox" name="remove_photo" value="1"> Eliminar fotografía actual</label>
    @endif
    <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
    <div class="hint">JPG, PNG, WEBP o GIF. Máximo 2 MB. {{ $product->exists ? 'Al subir una nueva se reemplaza la actual.' : '' }}</div>
</div>
<div class="actions"><button class="btn" type="submit">Guardar</button><a class="btn sec" href="{{ route('products.index') }}">Cancelar</a></div>
