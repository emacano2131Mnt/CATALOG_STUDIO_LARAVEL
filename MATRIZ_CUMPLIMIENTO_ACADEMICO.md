# Matriz de cumplimiento académico

Leyenda: ✅ verificado con prueba automatizada y/o ejecución real · 🟡 implementado, pero la parte de navegador (JS) requiere verificación manual · ❌ no cumplido.

Pruebas: **66 tests / 359 aserciones OK** (SQLite en memoria y MySQL 8.4.3 aislado). Migraciones verificadas en MySQL 8.4.3 real (subir/bajar/subir con datos previos).

| # | Requisito | Estado | Evidencia (código / prueba) |
|---|---|---|---|
| 1 | Proyecto Laravel 12 | ✅ | `composer.json` (`laravel/framework ^12`); instalado 12.x |
| 2 | Equipo de 1 a 3 integrantes documentado | 🟡 | `README.md` tabla de equipo (solo figura Miguel, tomado del usuario git: **complétese con el equipo real**) |
| 3 | Vistas con herencia de plantillas maestras | ✅ | `layouts/app`, `layouts/guest`; todas las vistas usan `@extends`; todas se renderizan en las pruebas |
| 4 | Base de datos MySQL | ✅ | `.env` MySQL 127.0.0.1:3307; migraciones verificadas en MySQL 8.4.3 |
| 5 | Landing page | ✅ | `welcome.blade.php` · `ExampleTest` (responsive por CSS; revisar visualmente) |
| 6 | Registro y login personalizados | ✅ | `Auth/RegisterController`, `Auth/LoginController`, vistas `auth/*` · `AuthTest` |
| 7 | Rutas protegidas | ✅ | `middleware('auth')` en `routes/web.php`, `auth:sanctum` en API · `AuthTest::test_guests_are_redirected…` |
| 8 | Migraciones de base de datos | ✅ | `database/migrations/*` (6 previas + 3 aditivas) · `docs/MIGRACIONES.md` |
| 9 | Modelos y controladores coherentes | ✅ | 4 modelos con relaciones, 4 policies/ recursos; controladores por entidad |
| 10 | Recuperación de contraseña | ✅ | `Auth/PasswordResetController` · `AuthTest::test_password_reset_flow` (el correo va al log por defecto) |
| 11 | Dashboard con identificación de usuario | ✅ | `DashboardController` + `dashboard/index` (nombre, correo, totales, recientes) · `CrudTest::test_dashboard_shows_real_user_data_and_chart` |
| 12 | Logout | ✅ | `LoginController@destroy` · `AuthTest::test_user_can_login_and_logout` |
| 13 | DataTables en dos tablas diferentes | 🟡 | `#products-table` y `#categories-table` + `partials/datatables` · pruebas verifican marcado y datos reales; el funcionamiento JS (buscar/ordenar/paginar) se verifica manualmente |
| 14 | Edición del perfil | ✅ | `ProfileController` · `AuthTest` (nombre, correo único, contraseña con la actual) |
| 15 | Al menos un gráfico de datos | 🟡 | Chart.js doughnut en dashboard · prueba verifica `<canvas>`, script y datos reales; el render visual se verifica manualmente |
| 16 | CRUD de al menos dos tablas | ✅ | Catálogos, productos y categorías · `CrudTest` |
| 17 | API propia consumible externamente | ✅ | `routes/api.php`, Sanctum, Resources · `ApiTest` (8 pruebas) · `docs/API.md` (no probada con la app Postman en sí, sí con peticiones HTTP de prueba) |
| 18 | Consumo de API de terceros | ✅ | `DummyJsonService` + `ImportController` · `ImportTest` con `Http::fake` (no se ejecutó contra internet real dentro de las pruebas) |
| 19 | Descarga de informes PDF | ✅ | `CatalogExportController`, `ReportController`, Dompdf · `DesignerAndPdfTest` (cabeceras `application/pdf`, `%PDF`, `attachment`) |
| 20 | Subida de imágenes | ✅ | `ProductImageService` · `CrudTest` (subida, reemplazo, borrado, rechazo de php/exe/pdf/svg/tamaño) |

## Requisitos del enunciado adicionales (módulos)

| Funcionalidad | Estado | Notas |
|---|---|---|
| Autorización por usuario (catálogos/productos/categorías/PDF/API/import) | ✅ | `AuthorizationTest` (10) + API |
| Duplicación de catálogos | ✅ | `CrudTest::test_catalog_duplication…` |
| Ordenamiento manual de productos | ✅ | Botones ↑↓ + campo posición · `CrudTest::test_manual_ordering…` |
| Filtros de productos por catálogo y categoría | ✅ | `CrudTest::test_product_filters…` |
| Diseñador (colores, fuentes, portada, columnas, márgenes, visibilidad, encabezado/pie) y 4 plantillas | ✅ | `DesignerAndPdfTest` |
| Vista previa funcional | 🟡 | La ruta de vista previa está probada; la actualización en vivo (JS) es manual |
| Exportación parcial validada | ✅ | `test_pdf_rejects_products_from_another_catalog…` |
| Sección que consume la API propia por HTTP | 🟡 | `/api-consumo` usa `fetch`; probado que la página lo declara y que la API responde con sesión; la ejecución JS es manual |
| Informes de productos en PDF | ✅ | `/informes/productos` |

## Pendientes / limitaciones declaradas

- Verificación manual en navegador de: DataTables, Chart.js, vista previa en vivo, `fetch` de `/api-consumo`, y fotos remotas dentro del PDF.
- No se probó la recepción real del correo de recuperación (driver `log`).
- Las migraciones nuevas **aún no están aplicadas en la BD real** (está en otro PC): ejecutar `php artisan migrate` allí (ver `docs/MIGRACIONES.md`).
- Datos del equipo (requisito 2) por completar.
- No se hicieron commits ni push.
