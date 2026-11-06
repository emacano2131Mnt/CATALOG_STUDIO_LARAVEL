<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_authentication_and_returns_json(): void
    {
        $this->getJson('/api/catalogs')->assertUnauthorized()->assertJsonStructure(['message']);
        $this->getJson('/api/products')->assertUnauthorized();
        $this->getJson('/api/categories')->assertUnauthorized();
        // Sin cabecera Accept también debe ser JSON (no redirección a /login).
        $this->get('/api/catalogs')->assertUnauthorized()->assertHeader('content-type', 'application/json');
    }

    public function test_bearer_token_authenticates(): void
    {
        $user = User::factory()->create();
        Catalog::factory()->for($user)->create(['name' => 'Con token']);
        $token = $user->createToken('postman')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/catalogs')->assertOk()->assertJsonPath('data.0.name', 'Con token');
        // Se reinicia el guard: dentro de una prueba la app es la misma y cachearía al usuario anterior.
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer invalido')->getJson('/api/catalogs')->assertUnauthorized();
    }

    public function test_catalog_endpoints_with_pagination_and_structure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $catalogs = Catalog::factory()->count(3)->for($user)->create();
        Product::factory()->count(4)->for($catalogs[0])->create();

        $this->getJson('/api/catalogs?per_page=2')->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'category', 'description', 'products_count', 'created_at']], 'links', 'meta' => ['current_page', 'per_page', 'total']])
            ->assertJsonPath('meta.total', 3);

        $this->getJson("/api/catalogs/{$catalogs[0]->id}")->assertOk()->assertJsonPath('data.products_count', 4);
        $this->getJson("/api/catalogs/{$catalogs[0]->id}/products?per_page=3")->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('meta.total', 4)
            ->assertJsonStructure(['data' => [['id', 'name', 'price', 'image_url', 'category']]]);
    }

    public function test_products_and_categories_endpoints_with_filters(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $catalog = Catalog::factory()->for($user)->create();
        $category = Category::factory()->for($user)->create(['name' => 'Cat A']);
        Product::factory()->for($catalog)->create(['name' => 'Zapato', 'category_id' => $category->id]);
        Product::factory()->for($catalog)->create(['name' => 'Gorra']);

        $this->getJson('/api/products')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/products?search=Zapato')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/products?category_id='.$category->id)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.category.name', 'Cat A');
        $this->getJson('/api/categories')->assertOk()->assertJsonPath('data.0.name', 'Cat A')->assertJsonPath('data.0.products_count', 1);
    }

    public function test_validation_errors_return_422(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/catalogs?per_page=9999')->assertStatus(422)->assertJsonValidationErrors('per_page');
        $this->getJson('/api/products?per_page=abc')->assertStatus(422);
    }

    public function test_not_found_returns_json_404(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/catalogs/99999')->assertNotFound()->assertJsonStructure(['message']);
        $this->getJson('/api/catalogs/abc')->assertNotFound();
    }

    public function test_api_never_exposes_other_users_data(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $foreign = Catalog::factory()->for($other)->create(['name' => 'Privado']);
        $foreignProduct = Product::factory()->for($foreign)->create(['name' => 'PrivadoProd']);
        Category::factory()->for($other)->create(['name' => 'CatPrivada']);
        Sanctum::actingAs($me);

        $this->getJson('/api/catalogs')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/catalogs/{$foreign->id}")->assertNotFound();
        $this->getJson("/api/catalogs/{$foreign->id}/products")->assertNotFound();
        $this->getJson("/api/products/{$foreignProduct->id}")->assertNotFound();
        $this->getJson('/api/products')->assertJsonCount(0, 'data');
        $this->getJson('/api/categories')->assertJsonCount(0, 'data');
        $this->getJson('/api/products?search=Privado')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
    }

    public function test_api_resources_do_not_leak_sensitive_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Catalog::factory()->for($user)->create();
        $body = $this->getJson('/api/catalogs')->getContent();
        $this->assertStringNotContainsString('user_id', $body);
        $this->assertStringNotContainsString($user->email, $body);
    }

    public function test_api_consumer_page_calls_the_http_api(): void
    {
        $this->actingAs(User::factory()->create())->get('/api-consumo')->assertOk()
            ->assertSee("fetch(url", false)->assertSee('/api/catalogs', false)->assertSee('/api/products', false);
    }

    public function test_session_cookie_can_call_the_api_from_same_origin(): void
    {
        $user = User::factory()->create();
        Catalog::factory()->for($user)->create(['name' => 'Via sesion']);
        $this->actingAs($user, 'web')->getJson('/api/catalogs')->assertOk()->assertJsonPath('data.0.name', 'Via sesion');
    }

    public function test_personal_tokens_can_be_created_and_revoked_from_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/perfil/tokens', ['token_name' => 'Insomnia'])->assertSessionHas('new_token');
        $this->assertSame(1, $user->tokens()->count());
        $this->actingAs($user)->delete('/perfil/tokens/'.$user->tokens()->first()->id)->assertRedirect();
        $this->assertSame(0, $user->tokens()->count());
    }
}
