# Documentación técnica — Catalog Studio

## 1. Descripción y justificación

Catalog Studio resuelve un problema común de pequeños comercios: mantener un catálogo de productos ordenado, con buena presentación y exportable (PDF) sin herramientas de diseño. Cada usuario gestiona sus propios catálogos, personaliza el diseño y los comparte en PDF.

## 2. Arquitectura MVC

```
routes/web.php, routes/api.php      → Rutas (web con sesión, api con Sanctum)
app/Http/Controllers/               → Controladores (C)
   Auth/ (Register, Login, PasswordReset)  Api/ (Catalog, Product, Category)
   Catalog, Product, Category, Dashboard, Profile, Designer, CatalogExport, Report, Import
app/Models/                         → Modelos Eloquent (M): User, Catalog, Category, Product
app/Policies/                       → Autorización por propiedad
app/Http/Requests/                  → Form Requests (validación + authorize)
app/Http/Resources/                 → API Resources (JSON)
app/Services/                       → DummyJsonService, ProductImageService, CatalogDesign
resources/views/                    → Vistas Blade (V) con herencia de plantilla
   layouts/app (panel con sidebar) · layouts/guest (auth) · components/navigation · partials/datatables
   pdf/catalog · pdf/products-report (plantillas Dompdf)
```

**Herencia de plantillas:** `layouts/app.blade.php` (`@extends` + `@yield('content')` + `@stack('scripts')`) para todo el panel; `layouts/guest.blade.php` para login/registro/recuperación; la landing (`welcome.blade.php`) es independiente pública. La navegación lateral se incluye una vez (`components/navigation`).

## 3. Modelos y relaciones

| Modelo | Relaciones |
|---|---|
| `User` | `hasMany Catalog`, `hasMany Category`, tokens Sanctum (`HasApiTokens`) |
| `Catalog` | `belongsTo User`, `hasMany Product` (ordenados por `position`); `design` casteado a array (JSON) |
| `Category` | `belongsTo User` (nullable = global), `hasMany Product` |
| `Product` | `belongsTo Catalog`, `belongsTo Category` (opcional); accessors `image_src`, `image_pdf_source` |

Scopes: `ownedBy($user)`, `visibleTo($user)` (categorías propias + globales), `search`, `inCatalog`, `inCategory`.

## 4. Migraciones

Ver [`docs/MIGRACIONES.md`](docs/MIGRACIONES.md) (justificación, cómo aplicarlas en el PC con la BD y cómo revertir).

## 5. Rutas principales

| Área | Rutas |
|---|---|
| Público | `/` (landing), `/registro`, `/login`, `/recuperar-contrasena`, `/restablecer-contrasena/{token}` |
| Auth (middleware `auth`) | `/dashboard`, `/perfil` (+ contraseña, tokens), `/catalogs`, `/products`, `/categories` (resource), `/catalogs/{c}/duplicate`, `/catalogs/{c}/pdf`, `/products/{p}/move`, `/disenador…`, `/importacion`, `/informes…`, `/api-consumo`, `POST /logout` |
| API (`auth:sanctum`) | `/api/catalogs`, `/api/catalogs/{id}`, `/api/catalogs/{id}/products`, `/api/products`, `/api/products/{id}`, `/api/categories` — ver [`docs/API.md`](docs/API.md) |

`php artisan route:list --except-vendor` lista las 55 rutas.

## 6. Autenticación

Implementación **propia** (sin Breeze, para no instalar Tailwind/Vite y mantener un único mecanismo): registro (contraseña con `Password::min(8)->letters()->numbers()`, cifrada con bcrypt vía cast `hashed`), login (rate limiting 5 intentos por correo+IP, regeneración de sesión), logout (invalida sesión y token CSRF), recuperación y restablecimiento con el *password broker* de Laravel (tabla `password_reset_tokens`; el mensaje no revela si el correo existe). Sesiones en BD (`SESSION_DRIVER=database`). Middleware `guest` en pantallas de auth y `auth` en el resto.

## 7. Seguridad y propiedad de los datos

Defensa en profundidad, **siempre en servidor**:

1. **Consultas restringidas:** listados con `ownedBy($user)`/`visibleTo($user)`; nunca se listan datos ajenos (dashboard, productos, categorías, diseñador, informes, API).
2. **Policies** (`CatalogPolicy`, `ProductPolicy`, `CategoryPolicy`) usadas con `$this->authorize()` en cada acción de show/edit/update/delete/duplicate/move/diseño/PDF. Un recurso ajeno → **403** (web) o **404** (API).
3. **Form Requests** autorizan antes de validar (`UpdateProductRequest::authorize` usa la policy).
4. **Validación de pertenencia en referencias:** `catalog_id` debe existir *y* ser del usuario (`Rule::exists(...)->where('user_id', …)`); `category_id` debe ser propia o global; no se puede mover un producto propio a un catálogo ajeno.
5. **PDF:** el catálogo se autoriza y cada `products[]` debe pertenecer *a ese catálogo* (`Rule::exists('products','id')->where('catalog_id', …)`); el informe valida sus filtros igual.
6. **Importación:** se autoriza el catálogo destino; los datos se re-consultan a la API en servidor.
7. CSRF en todos los formularios web, escape automático de Blade (`{{ }}`), consultas parametrizadas (Eloquent), throttle de la API (60/min).
8. **Categorías globales:** las preexistentes (sin dueño) se pueden usar pero no editar/eliminar.

Probado en `tests/Feature/AuthorizationTest.php` (10 pruebas con un segundo usuario atacante).

## 8. Carga de imágenes

`ProductImageService` (Storage, disco `public`, carpeta `storage/app/public/products`, enlace `public/storage` con `php artisan storage:link`):

- Validación: `image`, `mimes:jpg,jpeg,png,webp,gif`, `mimetypes:image/...` (MIME real), máx. **2 MB**. **SVG excluido** (puede contener scripts).
- Nombre seguro: UUID + extensión deducida del MIME; el nombre original nunca se usa.
- Reemplazo: guarda la nueva, luego borra la anterior. Opción "eliminar fotografía". Al borrar producto/catálogo se borran los archivos.
- `delete()` solo actúa dentro de `products/` (evita borrados arbitrarios). Duplicar un catálogo copia los archivos.
- Solo se almacenan imágenes en el directorio público; no hay ejecutables posibles por la validación de MIME/extensión.

## 9. Diseñador y PDF

- `CatalogDesign` define 4 plantillas (Minimalista, Premium, Comercial, Corporativa) y `resolve()` combina plantilla base + diseño guardado (`catalogs.design` JSON) + valores de vista previa. Opciones: colores (4), tipografía (5), título, subtítulo, portada y texto, encabezado/pie, columnas (1-4), tamaño de foto, márgenes, espaciado, moneda y visibilidad de precios/descripciones/categorías.
- **Vista previa funcional:** un `iframe` carga `/disenador/{c}/vista-previa?design[...]` con el formulario actual (sin guardar). Usa **la misma plantilla Blade** `pdf/catalog` que el PDF (fidelidad).
- **PDF (barryvdh/laravel-dompdf 3.x):** `/catalogs/{c}/pdf` (ver en el navegador) y `?download=1` (descargar); `?products[]=` para exportación parcial. Incluye fotos (archivo local o URL remota), precios y categorías según el diseño. Informe tabular: `/informes/productos` (filtros catálogo/categoría).
- Dompdf no soporta flex/grid: la distribución usa tablas HTML.

## 10. DataTables y gráfico

- `partials/datatables.blade.php` inicializa DataTables 1.13 (búsqueda, ordenamiento, paginación, selector 5/10/25/50/100, idioma español) sobre **dos tablas**: `#products-table` y `#categories-table`, con filas reales renderizadas desde Eloquent (procesamiento del lado del cliente).
- Dashboard: Chart.js (doughnut) "Productos por categoría" con datos de Eloquent (`withCount`) inyectados con `@json`. Sin datos → mensaje vacío, sin datos ficticios.

## 11. API propia y consumo

Ver [`docs/API.md`](docs/API.md). Consumo de la API propia: `/api-consumo` hace `fetch()` HTTP real a `/api/catalogs|products|categories` (cookie de sesión vía Sanctum stateful) y pinta la respuesta. Se eligió `fetch` desde el navegador (y no una llamada servidor→servidor) porque `php artisan serve` en Windows es de un solo hilo y una petición HTTP a sí mismo se bloquearía.

Consumo de API de terceros: [`docs/INTEGRACION_DUMMYJSON.md`](docs/INTEGRACION_DUMMYJSON.md).

## 12. Pruebas

Ejecutar: `php vendor/phpunit/phpunit/phpunit` (o `php artisan test`).

| Archivo | Cubre |
|---|---|
| `AuthTest` | registro (cifrado), validaciones, login, logout, credenciales erróneas, rutas protegidas, recuperación y reseteo (token válido/ inválido), perfil, contraseña |
| `CrudTest` | CRUD catálogos/productos/categorías, búsqueda, filtros, duplicado, subida/reemplazo/eliminación de fotos, validaciones de archivos (php, exe, pdf, svg, tamaño), orden manual, marcado DataTables, dashboard + datos del gráfico |
| `AuthorizationTest` | accesos cruzados entre usuarios (web) en catálogos, productos, categorías, diseñador, PDF, importación |
| `DesignerAndPdfTest` | diseñador, 4 plantillas, guardado y validación, vista previa, PDF completo/descarga/foto, exportación parcial, rechazo de productos ajenos, informe PDF |
| `ApiTest` | 401 JSON, token Bearer, paginación/estructura, filtros, 422, 404, no fuga de datos ajenos, página de consumo, tokens |
| `ImportTest` | DummyJSON simulado con `Http::fake()` |

**Resultado real:** 66 pruebas, 359 aserciones, todas OK — en SQLite en memoria y, aislado, en MySQL 8.4.3.

**SQLite:** la limitación previa (falta `pdo_sqlite`) no se da con el PHP de Laragon 8.3.33 (extensión activa). Si otro PC no la tiene, use la alternativa MySQL exclusiva documentada en [`docs/MIGRACIONES.md`](docs/MIGRACIONES.md).

**No cubierto por pruebas automáticas:** el comportamiento JavaScript en el navegador (DataTables, Chart.js, vista previa en vivo y `fetch` de /api-consumo) se verifica solo por el marcado servido; requiere comprobación manual (ver guía de sustentación). Tampoco se probó la descarga de imágenes remotas dentro del PDF.

## 13. Limitaciones conocidas

- DataTables procesa del lado del cliente (todas las filas del usuario); para decenas de miles de productos convendría paginación en servidor.
- El correo de recuperación usa el driver `log` por defecto.
- Las tablas de migración originales no se tocaron; cualquier categoría anterior queda global.
- Se agregó Sanctum y Dompdf (no estaban instalados); se usó el PHP de Laragon con `-d extension=zip` para `composer`.
