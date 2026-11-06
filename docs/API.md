# API REST de Catalog Studio

Base URL: `http://127.0.0.1:8000/api` · Formato: JSON · Siempre responde JSON (también 401/404/422).

## Autenticación

Laravel Sanctum.

1. Inicie sesión en la web → **Perfil → Tokens de la API** → *Crear token* (se muestra **una sola vez**).
2. En Postman/Insomnia: cabecera `Authorization: Bearer <token>` y `Accept: application/json`.

Además, las llamadas JS desde el propio sitio (sección **Consumo API**) se autentican con la cookie de sesión (`statefulApi`; dominios en `SANCTUM_STATEFUL_DOMAINS`).

## Privacidad

Todas las consultas se restringen al usuario autenticado. Los recursos de otros usuarios responden **404** (no 403) para no revelar su existencia. Los recursos no exponen `user_id` ni datos del usuario.

## Endpoints

| Método | Ruta | Descripción | Parámetros opcionales |
|---|---|---|---|
| GET | `/api/catalogs` | Catálogos propios paginados | `per_page` (1-100, def. 15), `search`, `page` |
| GET | `/api/catalogs/{id}` | Un catálogo (con `products_count`) | — |
| GET | `/api/catalogs/{id}/products` | Productos de un catálogo, ordenados | `per_page`, `page` |
| GET | `/api/products` | Productos propios | `per_page`, `search`, `catalog_id`, `category_id` |
| GET | `/api/products/{id}` | Un producto | — |
| GET | `/api/categories` | Categorías propias + globales (con `products_count` propio) | `per_page`, `search` |

## Códigos HTTP

| Código | Cuándo |
|---|---|
| 200 | Éxito |
| 401 | Sin token / token inválido (`{"message":"Unauthenticated."}`) |
| 404 | Recurso inexistente **o de otro usuario** |
| 422 | Parámetros inválidos (`{"message":..., "errors":{"per_page":[...]}}`) |
| 429 | Más de 60 peticiones por minuto |

## Ejemplo

```
GET /api/catalogs?per_page=2
Authorization: Bearer 1|abc...
Accept: application/json
```

```json
{
  "data": [
    { "id": 3, "name": "Verano", "category": "Ropa", "description": "…", "products_count": 4,
      "created_at": "2026-11-06T10:00:00+00:00", "updated_at": "2026-11-06T10:00:00+00:00" }
  ],
  "links": { "first": "…?page=1", "last": "…?page=2", "prev": null, "next": "…?page=2" },
  "meta": { "current_page": 1, "per_page": 2, "total": 3, "last_page": 2 }
}
```

`GET /api/catalogs/3/products` → cada producto: `id, catalog_id, name, description, price (número), position, image_url (absoluta o null), category {id,name,is_global}, created_at`.

## Probar con curl

```bash
curl -H "Accept: application/json" -H "Authorization: Bearer <token>" "http://127.0.0.1:8000/api/products?search=camisa"
```

## Implementación

`routes/api.php` · `app/Http/Controllers/Api/*` · `app/Http/Resources/*` (API Resources) · validación con `$request->validate()` · `bootstrap/app.php` fuerza respuestas JSON en `api/*`.
