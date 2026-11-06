# Integración con DummyJSON (API de terceros)

API pública: <https://dummyjson.com/products>. Sección de la app: **Importación** (`/importacion`).

## Diseño

| Pieza | Archivo | Responsabilidad |
|---|---|---|
| Servicio | `app/Services/DummyJsonService.php` | **Único** lugar que habla con la API externa (Laravel HTTP Client): `list(q, page, perPage)` y `find(id)`. Timeout 10 s, 2 reintentos, normaliza la respuesta, traduce errores a `RuntimeException` con mensaje en español. |
| Controlador | `app/Http/Controllers/ImportController.php` | `index` lista/busca (12 por página) y `store` importa. |
| Vista | `resources/views/import/index.blade.php` | Solo muestra datos ya obtenidos: **no hay peticiones externas en Blade**. |
| Config | `config/services.php` → `dummyjson.base_url` (env `DUMMYJSON_BASE_URL`) | |

## Flujo

1. `GET /importacion?q=&page=` → el servicio llama `GET /products?limit=12&skip=…` o `/products/search?q=…`. Se muestran foto, nombre, descripción, precio y categoría.
2. El usuario marca productos, elige **un catálogo propio** y pulsa *Importar*.
3. `POST /importacion` (`catalog_id`, `ids[]`, máx. 30):
   - Valida y autoriza que el catálogo sea del usuario (403 si no).
   - Por cada id: si ya existe `(catalog_id, external_id)` → **se omite** (sin duplicados; además hay índice único en BD).
   - Si no, **vuelve a consultar** `GET /products/{id}` (no se confía en datos del formulario).
   - Crea/reutiliza la categoría del usuario (`firstOrCreate` con el nombre formateado, p. ej. `home-decoration` → `Home Decoration`) y el producto (`image_url` = miniatura remota, `external_id`).
4. Resultado: mensaje con importados / omitidos / fallidos.

## Manejo de errores

- Sin conexión / timeout → alerta "No se pudo conectar con DummyJSON…" y enlace *Reintentar*; la página no se rompe.
- HTTP 5xx/4xx → "DummyJSON respondió con un error (HTTP n)".
- Producto inexistente (404) durante la importación → cuenta como fallido, el resto continúa.

## Pruebas

`tests/Feature/ImportTest.php` usa `Http::fake()` (no depende de internet): listado, búsqueda, importación, categorías, duplicados, errores de conexión/HTTP, validación.

## Limitación conocida

Las imágenes importadas se referencian por URL remota (no se descargan). En el PDF, Dompdf las descarga en el momento (`enable_remote=true` en `config/dompdf.php`); requiere internet al generar el PDF. No se probó la descarga remota dentro del PDF en el entorno de desarrollo (sin acceso verificado a internet).
