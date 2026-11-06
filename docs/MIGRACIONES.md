# Migraciones y base de datos (BD en otro PC)

La BD real (`catalog_studio_laravel`, MySQL 8.4, `127.0.0.1:3307`, usuario `root`) **no está en el PC donde se desarrolló** esta fase. Por eso:

- Todo cambio de esquema se hizo con **migraciones aditivas e idempotentes** (comprueban `hasColumn`/`hasTable` antes de alterar).
- Ninguna migración borra filas ni columnas existentes.
- Las migraciones se **verificaron en un MySQL 8.4.3 real y aislado** (instancia temporal en otro puerto y datadir), simulando el estado previo: se aplicaron las 6 migraciones anteriores, se insertaron datos "legados" y luego se aplicaron las nuevas. Resultado: sin errores, datos conservados, y `rollback` + re-aplicar también correcto.

## Estado esperado de la BD real

Ya aplicadas (fase anterior, Codex): `users/password_reset_tokens/sessions`, `cache`, `jobs`, `catalogs`, `categories`, `products`.

**Pendientes de aplicar en la BD real (esta fase):**

| Migración | Qué hace | Por qué |
|---|---|---|
| `2026_11_06_100000_add_user_id_to_categories_table` | Agrega `categories.user_id` (nullable, FK → users, `nullOnDelete`). Reemplaza el índice único `name` por único `(user_id, name)`. | Cada usuario debe administrar solo sus categorías y dos usuarios pueden usar el mismo nombre. Las categorías existentes quedan con `user_id = NULL` = **globales** (todos las ven y usan; nadie las edita/elimina). |
| `2026_11_06_100001_add_import_fields_to_products_table` | Agrega `products.external_id` y `products.image_url` (nullable) + único `(catalog_id, external_id)`. | Importación DummyJSON sin duplicados e imagen remota. Los productos manuales dejan `external_id` NULL (MySQL permite varios NULL en un único). |
| `2026_11_06_100002_create_personal_access_tokens_table` | Tabla de Laravel Sanctum. Se omite si ya existe. | Tokens personales para la API REST. |

## Aplicar en el PC con la BD

```bash
php artisan migrate:status      # verifique qué está pendiente
php artisan migrate --pretend   # (opcional) muestra el SQL sin ejecutarlo
php artisan migrate             # aplica solo las 3 pendientes
```

Recomendación: **respalde** antes (`mysqldump -P3307 -uroot catalog_studio_laravel > respaldo.sql`).

## Revertir (si hace falta)

```bash
php artisan migrate:rollback --step=3
```

Advertencia: al revertir `100000`, se restaura el índice único global `name`; si dos usuarios ya crearon categorías con el mismo nombre, MySQL rechazará la reversión (es el comportamiento seguro: no pierde datos).

## Comandos que NO debe ejecutar sobre la BD real

`migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`, `db:seed` (el seeder por defecto crea `test@example.com`).

## Base de datos de pruebas

Las pruebas **no usan** la BD real:

- Por defecto: SQLite en memoria (`phpunit.xml`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`). Requiere la extensión `pdo_sqlite` (activa en Laragon PHP 8.3.33; en `php.ini`: `extension=pdo_sqlite` y `extension=sqlite3`).
- Alternativa si el PC no tiene `pdo_sqlite`: cree una BD **exclusiva** `catalog_studio_testing` y ejecute:
  ```powershell
  $env:DB_CONNECTION='mysql'; $env:DB_HOST='127.0.0.1'; $env:DB_PORT='3307'
  $env:DB_DATABASE='catalog_studio_testing'; $env:DB_USERNAME='root'; $env:DB_PASSWORD=''
  php vendor/phpunit/phpunit/phpunit
  ```
  `RefreshDatabase` ejecuta `migrate:fresh` sobre **esa** BD: nunca apunte `DB_DATABASE` a `catalog_studio_laravel`.
- Las 66 pruebas se ejecutaron con éxito en ambos motores (SQLite y MySQL 8.4.3 aislado).
