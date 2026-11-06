# Catalog Studio (Laravel 12)

Aplicación web para crear, diseñar y exportar a PDF catálogos de productos. Proyecto académico desarrollado con **Laravel 12 / PHP 8.3 / MySQL 8.4 / Blade**, con arquitectura MVC.

> Ubicación del proyecto: `C:\Proyectos\Dllo web\CATALOG_STUDIO_LARAVEL`
> (el prompt original indicaba `C:\laragon\www\catalog_studio_laravel`, que **no existe** en este equipo, por lo que se usó esta carpeta).

## Equipo

| Integrante | Rol |
|---|---|
| Miguel | Desarrollo, base de datos y documentación |

> Equipo de 1 a 3 integrantes. Si hay más compañeros, agréguelos en esta tabla antes de la sustentación.

## Objetivos

- Permitir a cada usuario administrar **sus** catálogos, productos y categorías.
- Personalizar el diseño del catálogo (4 plantillas) y exportarlo a PDF (completo o parcial).
- Exponer una **API REST propia** y consumir **una API de terceros** (DummyJSON).

## Alcance

Landing pública · registro/login/logout/recuperación de contraseña · perfil · dashboard con gráfico (Chart.js) · CRUD de catálogos, productos y categorías · DataTables · subida de fotografías · duplicación de catálogos · diseñador · PDF · informes · API REST (Sanctum) · consumo de API propia · importación desde DummyJSON.

## Tecnologías

Laravel 12.x · PHP 8.3 · MySQL 8.4 · Blade · Eloquent · Laravel Sanctum (tokens API) · barryvdh/laravel-dompdf (PDF) · Laravel HTTP Client · Chart.js 4 · jQuery + DataTables 1.13 · Bootstrap Icons (CDN). **No requiere Node/Vite**: el CSS (`public/css/studio.css`) y las librerías JS se sirven estáticamente / por CDN.

## Instalación (PC con la base de datos)

La base de datos MySQL **no está en el PC de desarrollo**, sino en otro equipo. Siga estos pasos en el equipo donde está MySQL (o configure `DB_HOST` hacia él). Detalle completo en [`docs/MIGRACIONES.md`](docs/MIGRACIONES.md).

`.env.example` **ya trae toda la configuración** (MySQL `127.0.0.1:3307`, BD `catalog_studio_laravel`, usuario `root`, sin contraseña, idioma, correo por log, DummyJSON y Sanctum). No hay que editar nada si el entorno es el confirmado. Requisito: la BD `catalog_studio_laravel` ya existe (las migraciones crean las tablas, no la BD).

```bash
composer install
copy .env.example .env          # (Linux/Mac: cp)
php artisan key:generate
php artisan migrate             # SOLO aplica las migraciones pendientes (aditivas, no borra datos)
php artisan storage:link        # enlace public/storage para las fotografías
php artisan serve               # http://127.0.0.1:8000
```

> **No ejecute** `php artisan db:seed` ni `migrate:fresh` en la base real: el seeder de Laravel crea un usuario de prueba y `migrate:fresh` borra todo.

Nota Windows/Laragon: si `composer` indica que falta la extensión `zip`, ejecute `php -d extension=zip composer.phar install`.

## Ejecución

- Aplicación: `php artisan serve` → http://127.0.0.1:8000
- Pruebas: `php vendor/phpunit/phpunit/phpunit` (o `php artisan test`). Usan **SQLite en memoria** (`phpunit.xml`), nunca la BD real. Ver "Pruebas" en [`DOCUMENTACION_TECNICA.md`](DOCUMENTACION_TECNICA.md).
- Recuperación de contraseña: el correo se escribe en `storage/logs/laravel.log` (`MAIL_MAILER=log`); copie el enlace desde allí. Para correo real configure `MAIL_*` en `.env`.

## Documentación

| Archivo | Contenido |
|---|---|
| [`DOCUMENTACION_TECNICA.md`](DOCUMENTACION_TECNICA.md) | Arquitectura MVC, modelos, migraciones, controladores, seguridad, PDF, imágenes, pruebas |
| [`docs/MIGRACIONES.md`](docs/MIGRACIONES.md) | Cómo aplicar/revertir las migraciones en el PC que tiene la BD |
| [`docs/API.md`](docs/API.md) | Endpoints de la API REST con ejemplos (Postman/Insomnia) |
| [`docs/INTEGRACION_DUMMYJSON.md`](docs/INTEGRACION_DUMMYJSON.md) | Cómo funciona la importación desde DummyJSON |
| [`GUIA_SUSTENTACION.md`](GUIA_SUSTENTACION.md) | Demo de ~10 min y preguntas técnicas probables |
| [`MATRIZ_CUMPLIMIENTO_ACADEMICO.md`](MATRIZ_CUMPLIMIENTO_ACADEMICO.md) | Cumplimiento de los 20 requisitos con evidencia |
