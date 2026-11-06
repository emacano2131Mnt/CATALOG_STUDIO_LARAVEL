# Guía de sustentación — Catalog Studio

## Preparación (antes de entrar)

1. En el PC con la BD: `php artisan migrate` (ver `docs/MIGRACIONES.md`), `php artisan storage:link`, `php artisan serve`.
2. Tener 2 cuentas de prueba (para demostrar aislamiento) y abrir `storage/logs/laravel.log` (correo de recuperación).
3. Tener Postman/Insomnia abierto con la colección de `docs/API.md`.
4. Conexión a internet (DummyJSON y CDN de Chart.js/DataTables).

## Demostración práctica (~10 minutos)

| Min | Qué mostrar | Requisito |
|---|---|---|
| 0:00 | **Landing** pública (responsive: reduzca la ventana). Botones Registrarse/Iniciar sesión. | Landing |
| 0:45 | **Registro** de un usuario nuevo → entra al dashboard (contraseña cifrada: mostrar en BD el hash bcrypt). | Registro, sesión |
| 1:30 | **Dashboard**: nombre y correo, 3 tarjetas, gráfico (vacío al inicio, "sin datos ficticios"). | Dashboard, gráfico |
| 2:00 | **Categorías**: crear 2 → DataTable (buscar, ordenar, cambiar cantidad). | CRUD, DataTables #1 |
| 2:45 | **Catálogos**: crear "Colección Verano" → ver → editar. | CRUD |
| 3:15 | **Productos**: crear 3 con foto (probar subir un `.php` renombrado/PDF para mostrar el rechazo); filtros por catálogo/categoría; DataTable; reemplazar foto. | CRUD, imágenes, DataTables #2 |
| 4:30 | En el catálogo: ordenar con ↑↓ y **duplicar** el catálogo. | Orden manual, duplicación |
| 5:00 | **Dashboard** otra vez: el gráfico ya refleja la distribución real. | Gráfico |
| 5:30 | **Diseñador**: cambiar plantilla (4), colores, columnas; la vista previa cambia en vivo; guardar. | Diseñador |
| 6:30 | **PDF**: ver y descargar el catálogo; marcar 2 productos y exportar solo esos; **Informes** → PDF de productos. | PDF, informe |
| 7:30 | **Importación**: DummyJSON, marcar 3, importar a un catálogo; repetir → "omitidos por duplicado". | API de terceros |
| 8:30 | **Perfil**: editar nombre/correo, cambiar contraseña, **crear token**. **Consumo API**: pulsar Consultar. En Postman: `GET /api/catalogs` con Bearer. | API propia + consumo |
| 9:15 | **Seguridad**: con la 2ª cuenta, abrir por URL un catálogo de la 1ª (`/catalogs/ID`) → 403; en Postman `/api/catalogs/ID` → 404. | Autorización |
| 9:45 | **Cerrar sesión** y probar `/dashboard` → redirige a login. Recuperar contraseña (link en `laravel.log`). | Logout, rutas protegidas, recuperación |

Pruebas automatizadas (1 min, si hay tiempo): `php vendor/phpunit/phpunit/phpunit` → 66 pruebas OK.

## Preguntas técnicas probables

1. **¿Dónde está el MVC?** Rutas→Controladores (`app/Http/Controllers`)→Modelos (`app/Models`)→Vistas Blade (`resources/views`). Lógica de negocio reutilizable en `app/Services`.
2. **¿Cómo funciona la herencia de plantillas?** `@extends('layouts.app')`, `@section('content')`, `@yield`, `@stack('scripts')`, `@include`.
3. **¿Cómo se cifran las contraseñas?** Cast `hashed` del modelo `User` (bcrypt); nunca se guardan en claro; `Hash::check` al autenticar.
4. **¿Cómo evita que un usuario vea datos de otro?** Consultas con `ownedBy`, Policies (`authorize`), Form Requests que validan `catalog_id` del usuario, y 404 en la API. Es en servidor, no solo botones ocultos.
5. **¿Qué es una Policy y dónde se registra?** Clase de autorización por modelo; `Gate::policy()` en `AppServiceProvider`.
6. **¿Qué hace la migración de categorías y por qué?** Agrega `user_id` para propiedad; las existentes quedan globales; aditiva, sin perder datos.
7. **¿Por qué `down()` borra la FK antes del índice?** MySQL no permite borrar el índice que usa una FK (error 1553; lo detectamos probando en MySQL real).
8. **¿Cómo se suben las imágenes de forma segura?** Validación de MIME real y extensión, 2 MB, SVG excluido, nombre UUID, Storage `public`, borrado controlado.
9. **¿Cómo funciona la recuperación de contraseña?** Token en `password_reset_tokens` → notificación (log) → formulario con token+correo → `Password::reset`; expira a los 60 min.
10. **¿Qué es Sanctum y por qué se instaló?** Autenticación API con tokens personales (y cookie para el mismo sitio); necesario para proteger información privada en la API.
11. **¿Por qué la API responde 404 y no 403 en recursos ajenos?** Para no revelar que existen.
12. **¿Cómo se consume la API propia?** `fetch()` HTTP desde `/api-consumo`, sin tocar Eloquent en esa página.
13. **¿Dónde está la integración con DummyJSON?** `DummyJsonService` (HTTP Client, timeout, reintentos, errores); ni Blade ni el controlador hacen HTTP directo.
14. **¿Cómo evita duplicar al importar?** Consulta previa por `(catalog_id, external_id)` + índice único en BD.
15. **¿Cómo genera el PDF?** Dompdf renderiza el Blade `pdf/catalog` (mismo que la vista previa) con el diseño guardado en JSON.
16. **¿Cómo se valida la exportación parcial?** `products.*` debe existir con `catalog_id` = el catálogo, y el catálogo se autoriza.
17. **¿Qué diferencia hay entre DataTables del cliente y del servidor?** Aquí se usa cliente (filas reales en el HTML); para volúmenes grandes se usaría `serverSide` con un endpoint JSON.
18. **¿De dónde salen los datos del gráfico?** `Category::withCount(['products' => ownedBy])` en `DashboardController`, enviados con `@json` a Chart.js.
19. **¿Cómo prueban sin dañar la BD?** SQLite en memoria (`phpunit.xml`) o una BD MySQL exclusiva de testing; `RefreshDatabase`.
20. **¿Qué limitaciones tiene?** DataTables en cliente, correo en log, la prueba de JS es manual, imágenes remotas en PDF dependen de internet.

## Plan B si algo falla en vivo

- Sin internet: la importación mostrará el error controlado (demuestre el manejo de errores); gráfico/DataTables no cargarán (CDN) — muestre las pruebas automáticas.
- Si el PDF tarda por imágenes remotas: use productos con foto subida.
