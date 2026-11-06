<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Product;
use App\Models\User;
use App\Services\DummyJsonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    private function fakeApi(): void
    {
        Http::fake([
            'dummyjson.com/products/search*' => Http::response(['products' => [['id' => 7, 'title' => 'Buscado', 'description' => 'd', 'price' => 5.5, 'category' => 'beauty', 'thumbnail' => 'https://cdn.dummyjson.com/7.png']], 'total' => 1, 'skip' => 0, 'limit' => 12]),
            'dummyjson.com/products/1' => Http::response(['id' => 1, 'title' => 'Essence Mascara', 'description' => 'Lash mascara', 'price' => 9.99, 'category' => 'beauty', 'thumbnail' => 'https://cdn.dummyjson.com/1.png']),
            'dummyjson.com/products/2' => Http::response(['id' => 2, 'title' => 'Sofa', 'description' => 'Big', 'price' => 300, 'category' => 'home-decoration', 'thumbnail' => 'https://cdn.dummyjson.com/2.png']),
            'dummyjson.com/products/999' => Http::response(['message' => 'not found'], 404),
            'dummyjson.com/products*' => Http::response(['products' => [
                ['id' => 1, 'title' => 'Essence Mascara', 'description' => 'Lash mascara', 'price' => 9.99, 'category' => 'beauty', 'thumbnail' => 'https://cdn.dummyjson.com/1.png'],
                ['id' => 2, 'title' => 'Sofa', 'description' => 'Big', 'price' => 300, 'category' => 'home-decoration', 'thumbnail' => 'https://cdn.dummyjson.com/2.png'],
            ], 'total' => 30, 'skip' => 0, 'limit' => 12]),
        ]);
    }

    public function test_lists_external_products_with_photo_name_description_and_price(): void
    {
        $this->fakeApi();
        $this->get('/importacion')->assertOk()
            ->assertSee('Essence Mascara')->assertSee('Lash mascara')->assertSee('9.99')->assertSee('https://cdn.dummyjson.com/1.png', false);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'dummyjson.com/products') && $r['limit'] == 12);
    }

    public function test_search_uses_search_endpoint(): void
    {
        $this->fakeApi();
        $this->get('/importacion?q=mascara')->assertOk()->assertSee('Buscado');
        Http::assertSent(fn ($r) => str_contains($r->url(), 'products/search') && $r['q'] === 'mascara');
    }

    public function test_imports_selected_products_with_categories_and_skips_duplicates(): void
    {
        $this->fakeApi();
        $catalog = Catalog::factory()->for($this->user)->create();

        $this->post('/importacion', ['catalog_id' => $catalog->id, 'ids' => [1, 2]])->assertRedirect('/importacion')->assertSessionHas('status');

        $this->assertSame(2, $catalog->products()->count());
        $mascara = Product::where('external_id', 1)->firstOrFail();
        $this->assertSame('Essence Mascara', $mascara->name);
        $this->assertSame('9.99', $mascara->price);
        $this->assertSame('https://cdn.dummyjson.com/1.png', $mascara->image_url);
        $this->assertSame('Beauty', $mascara->category->name);
        $this->assertSame('Home Decoration', Product::where('external_id', 2)->first()->category->name);
        $this->assertSame($this->user->id, $mascara->category->user_id);

        // Segunda importación: no se duplica nada.
        $this->post('/importacion', ['catalog_id' => $catalog->id, 'ids' => [1, 2]])->assertSessionHas('error');
        $this->assertSame(2, $catalog->products()->count());
        $this->assertSame(1, $this->user->categories()->where('name', 'Beauty')->count());
    }

    public function test_connection_errors_are_handled_on_listing(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->get('/importacion')->assertOk()->assertSee('No se pudo conectar con DummyJSON');
    }

    public function test_http_errors_are_handled_on_listing(): void
    {
        Http::fake(['dummyjson.com/*' => Http::response('boom', 500)]);
        $this->get('/importacion')->assertOk()->assertSee('DummyJSON respondió con un error');
    }

    public function test_failures_during_import_are_counted_without_crashing(): void
    {
        $this->fakeApi();
        $catalog = Catalog::factory()->for($this->user)->create();

        $this->post('/importacion', ['catalog_id' => $catalog->id, 'ids' => [999, 1]])->assertSessionHas('status');
        $this->assertSame(1, $catalog->products()->count());

        Http::fake(fn () => throw new ConnectionException('offline'));
        $this->post('/importacion', ['catalog_id' => $catalog->id, 'ids' => [2]])->assertSessionHas('error');
        $this->assertSame(1, $catalog->products()->count());
    }

    public function test_import_validates_input(): void
    {
        $this->post('/importacion', [])->assertSessionHasErrors(['catalog_id', 'ids']);
        $this->post('/importacion', ['catalog_id' => 1, 'ids' => range(1, 40)])->assertSessionHasErrors('ids');
    }

    public function test_service_is_used_from_the_container_without_blade_http_calls(): void
    {
        $this->fakeApi();
        $result = app(DummyJsonService::class)->list();
        $this->assertCount(2, $result['products']);
        $this->assertSame(30, $result['total']);
        $this->assertNull(app(DummyJsonService::class)->find(999));
    }
}
