<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Verifica que la autorización se aplica en el servidor y no solo ocultando botones. */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $me;
    private User $other;
    private Catalog $foreignCatalog;
    private Product $foreignProduct;
    private Category $foreignCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->me = User::factory()->create();
        $this->other = User::factory()->create();
        $this->foreignCatalog = Catalog::factory()->for($this->other)->create(['name' => 'Secreto']);
        $this->foreignProduct = Product::factory()->for($this->foreignCatalog)->create(['name' => 'ProductoAjeno']);
        $this->foreignCategory = Category::factory()->for($this->other)->create(['name' => 'CategoriaAjena']);
        $this->actingAs($this->me);
    }

    public function test_cannot_view_edit_update_delete_or_duplicate_foreign_catalog(): void
    {
        $id = $this->foreignCatalog->id;
        $this->get("/catalogs/{$id}")->assertForbidden();
        $this->get("/catalogs/{$id}/edit")->assertForbidden();
        $this->put("/catalogs/{$id}", ['name' => 'Hackeado'])->assertForbidden();
        $this->delete("/catalogs/{$id}")->assertForbidden();
        $this->post("/catalogs/{$id}/duplicate")->assertForbidden();
        $this->assertSame('Secreto', $this->foreignCatalog->fresh()->name);
    }

    public function test_cannot_view_edit_update_delete_or_move_foreign_product(): void
    {
        $id = $this->foreignProduct->id;
        $this->get("/products/{$id}")->assertForbidden();
        $this->get("/products/{$id}/edit")->assertForbidden();
        $this->put("/products/{$id}", ['catalog_id' => $this->foreignCatalog->id, 'name' => 'X', 'price' => 1])->assertForbidden();
        $this->delete("/products/{$id}")->assertForbidden();
        $this->patch("/products/{$id}/move", ['direction' => 'up'])->assertForbidden();
        $this->assertModelExists($this->foreignProduct);
    }

    public function test_cannot_create_product_in_foreign_catalog(): void
    {
        $this->post('/products', ['catalog_id' => $this->foreignCatalog->id, 'name' => 'Intruso', 'price' => 1])->assertSessionHasErrors('catalog_id');
        $this->assertDatabaseMissing('products', ['name' => 'Intruso']);
    }

    public function test_cannot_move_own_product_into_foreign_catalog(): void
    {
        $mine = Product::factory()->for(Catalog::factory()->for($this->me))->create();
        $this->put("/products/{$mine->id}", ['catalog_id' => $this->foreignCatalog->id, 'name' => 'X', 'price' => 1])->assertSessionHasErrors('catalog_id');
        $this->assertNotSame($this->foreignCatalog->id, $mine->fresh()->catalog_id);
    }

    public function test_cannot_use_foreign_category(): void
    {
        $catalog = Catalog::factory()->for($this->me)->create();
        $this->post('/products', ['catalog_id' => $catalog->id, 'category_id' => $this->foreignCategory->id, 'name' => 'X', 'price' => 1])->assertSessionHasErrors('category_id');
    }

    public function test_cannot_manage_foreign_category(): void
    {
        $id = $this->foreignCategory->id;
        $this->get("/categories/{$id}")->assertForbidden();
        $this->get("/categories/{$id}/edit")->assertForbidden();
        $this->put("/categories/{$id}", ['name' => 'X'])->assertForbidden();
        $this->delete("/categories/{$id}")->assertForbidden();
    }

    public function test_global_categories_are_usable_but_read_only(): void
    {
        $global = Category::factory()->create(['user_id' => null, 'name' => 'Global']);
        $this->get('/categories')->assertSee('Global');
        $this->get("/categories/{$global->id}")->assertOk();
        $this->put("/categories/{$global->id}", ['name' => 'X'])->assertForbidden();
        $this->delete("/categories/{$global->id}")->assertForbidden();

        $catalog = Catalog::factory()->for($this->me)->create();
        $this->post('/products', ['catalog_id' => $catalog->id, 'category_id' => $global->id, 'name' => 'P', 'price' => 1])->assertSessionHasNoErrors();
    }

    public function test_lists_never_leak_foreign_data(): void
    {
        $this->get('/catalogs')->assertDontSee('Secreto');
        $this->get('/products')->assertDontSee('ProductoAjeno');
        $this->get('/categories')->assertDontSee('CategoriaAjena');
        $this->get('/dashboard')->assertDontSee('Secreto')->assertDontSee('ProductoAjeno');
        $this->get('/disenador')->assertDontSee('Secreto');
        $this->get('/informes')->assertDontSee('Secreto');
    }

    public function test_cannot_import_into_foreign_catalog(): void
    {
        $this->post('/importacion', ['catalog_id' => $this->foreignCatalog->id, 'ids' => [1]])->assertForbidden();
    }

    public function test_cannot_access_foreign_designer_or_export(): void
    {
        $id = $this->foreignCatalog->id;
        $this->get("/disenador/{$id}")->assertForbidden();
        $this->put("/disenador/{$id}", ['design' => ['template' => 'premium']])->assertForbidden();
        $this->get("/disenador/{$id}/vista-previa")->assertForbidden();
        $this->get("/catalogs/{$id}/pdf")->assertForbidden();
        $this->get("/catalogs/{$id}/pdf?products[]={$this->foreignProduct->id}")->assertForbidden();
    }
}
