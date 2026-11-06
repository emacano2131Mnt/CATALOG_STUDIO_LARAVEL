@csrf
<div class="field"><label for="name">Nombre *</label><input id="name" type="text" name="name" value="{{ old('name', $catalog->name ?? '') }}" required maxlength="120"></div>
<div class="field"><label for="category">Categoría de referencia</label><input id="category" type="text" name="category" value="{{ old('category', $catalog->category ?? '') }}" maxlength="80"><div class="hint">Texto libre para describir el tipo de catálogo (opcional).</div></div>
<div class="field"><label for="description">Descripción</label><textarea id="description" name="description" rows="4">{{ old('description', $catalog->description ?? '') }}</textarea></div>
<div class="actions"><button class="btn" type="submit">Guardar</button><a class="btn sec" href="{{ route('catalogs.index') }}">Cancelar</a></div>
