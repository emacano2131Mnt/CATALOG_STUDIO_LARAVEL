<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Storage::fake('public');
    }

    public function test_catalog_crud_and_search(): void
    {
        $this->post('/catalogs', ['name' => 'Verano', 'description' => 'Ropa'])->assertRedirect();
        $catalog = Catalog::firstOrFail();
        $this->assertSame($this->user->id, $catalog->user_id);

        $this->get('/catalogs')->assertOk()->assertSee('Verano');
        $this->get('/catalogs?search=Verano')->assertSee('Verano');
        $this->get('/catalogs?search=zzz')->assertDontSee('Verano');
        $this->get("/catalogs/{$catalog->id}")->assertOk();
        $this->get("/catalogs/{$catalog->id}/edit")->assertOk();

        $this->put("/catalogs/{$catalog->id}", ['name' => 'Invierno'])->assertRedirect();
        $this->assertSame('Invierno', $catalog->fresh()->name);

        $this->delete("/catalogs/{$catalog->id}")->assertRedirect('/catalogs');
        $this->assertModelMissing($catalog);
    }

    public function test_catalog_requires_name(): void
    {
        $this->post('/catalogs', ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_catalog_duplication_copies_products_and_images(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create(['name' => 'Base', 'design' => ['template' => 'premium']]);
        Storage::disk('public')->put('products/original.jpg', 'x');
        Product::factory()->for($catalog)->create(['name' => 'A', 'image' => 'products/original.jpg', 'external_id' => 5]);

        $this->post("/catalogs/{$catalog->id}/duplicate")->assertRedirect();

        $copy = Catalog::where('name', 'Base (copia)')->firstOrFail();
        $this->assertSame('premium', $copy->design['template']);
        $this->assertCount(1, $copy->products);
        $copyProduct = $copy->products->first();
        $this->assertNotSame('products/original.jpg', $copyProduct->image);
        Storage::disk('public')->assertExists($copyProduct->image);
        $this->assertNull($copyProduct->external_id);
        $this->assertCount(1, $catalog->fresh()->products);
    }

    public function test_category_crud_with_unique_name_per_user(): void
    {
        $this->post('/categories', ['name' => 'Ropa'])->assertRedirect();
        $category = Category::firstOrFail();
        $this->assertSame($this->user->id, $category->user_id);

        $this->post('/categories', ['name' => 'Ropa'])->assertSessionHasErrors('name');
        // Otro usuario sí puede usar el mismo nombre.
        $this->actingAs(User::factory()->create())->post('/categories', ['name' => 'Ropa'])->assertSessionHasNoErrors();

        $this->actingAs($this->user)->put("/categories/{$category->id}", ['name' => 'Calzado'])->assertRedirect();
        $this->assertSame('Calzado', $category->fresh()->name);
        $this->get("/categories/{$category->id}")->assertOk();
        $this->delete("/categories/{$category->id}")->assertRedirect('/categories');
        $this->assertModelMissing($category);
    }

    public function test_deleting_category_keeps_products_uncategorized(): void
    {
        $category = Category::factory()->for($this->user)->create();
        $product = Product::factory()->for(Catalog::factory()->for($this->user))->create(['category_id' => $category->id]);

        $this->delete("/categories/{$category->id}");

        $this->assertNull($product->fresh()->category_id);
    }

    public function test_product_crud_with_photo_upload_replace_and_delete(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create();
        $category = Category::factory()->for($this->user)->create();

        $this->post('/products', [
            'catalog_id' => $catalog->id, 'category_id' => $category->id, 'name' => 'Camisa', 'price' => '19.90',
            'photo' => UploadedFile::fake()->image('foto original.jpg', 200, 200),
        ])->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertSame('19.90', $product->price);
        $this->assertStringStartsWith('products/', $product->image);
        $this->assertStringNotContainsString('foto', $product->image, 'No se reutiliza el nombre original');
        Storage::disk('public')->assertExists($product->image);
        $old = $product->image;

        $this->put("/products/{$product->id}", ['catalog_id' => $catalog->id, 'name' => 'Camisa 2', 'price' => 25, 'photo' => UploadedFile::fake()->image('n.png')])->assertRedirect();
        $product->refresh();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($product->image);
        $this->assertStringEndsWith('.png', $product->image);

        $this->get("/products/{$product->id}")->assertOk()->assertSee('Camisa 2');
        $this->get("/products/{$product->id}/edit")->assertOk();

        $image = $product->image;
        $this->delete("/products/{$product->id}")->assertRedirect('/products');
        Storage::disk('public')->assertMissing($image);
        $this->assertModelMissing($product);
    }

    public function test_remove_photo_option(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create();
        Storage::disk('public')->put('products/a.jpg', 'x');
        $product = Product::factory()->for($catalog)->create(['image' => 'products/a.jpg']);

        $this->put("/products/{$product->id}", ['catalog_id' => $catalog->id, 'name' => 'P', 'price' => 1, 'remove_photo' => 1]);

        $this->assertNull($product->fresh()->image);
        Storage::disk('public')->assertMissing('products/a.jpg');
    }

    public function test_photo_validation_rejects_bad_files(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create();
        $base = ['catalog_id' => $catalog->id, 'name' => 'P', 'price' => 1];

        $this->post('/products', $base + ['photo' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')])->assertSessionHasErrors('photo');
        $this->post('/products', $base + ['photo' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream')])->assertSessionHasErrors('photo');
        $this->post('/products', $base + ['photo' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])->assertSessionHasErrors('photo');
        $this->post('/products', $base + ['photo' => UploadedFile::fake()->image('grande.jpg')->size(3000)])->assertSessionHasErrors('photo');
        $this->post('/products', $base + ['photo' => UploadedFile::fake()->create('dibujo.svg', 5, 'image/svg+xml')])->assertSessionHasErrors('photo');
        $this->assertSame(0, Product::count());
    }

    public function test_product_filters_and_datatables_markup(): void
    {
        $catalogA = Catalog::factory()->for($this->user)->create();
        $catalogB = Catalog::factory()->for($this->user)->create();
        $category = Category::factory()->for($this->user)->create();
        Product::factory()->for($catalogA)->create(['name' => 'Alfa', 'category_id' => $category->id]);
        Product::factory()->for($catalogB)->create(['name' => 'Beta']);

        $response = $this->get('/products')->assertOk();
        $response->assertSee('id="products-table"', false)->assertSee('Alfa')->assertSee('Beta')->assertSee('DataTable', false);

        $this->get('/products?catalog_id='.$catalogA->id)->assertSee('Alfa')->assertDontSee('Beta');
        $this->get('/products?category_id='.$category->id)->assertSee('Alfa')->assertDontSee('Beta');

        Category::factory()->for($this->user)->create(['name' => 'Zapatos']);
        $this->get('/categories')->assertOk()->assertSee('id="categories-table"', false)->assertSee('Zapatos')->assertSee('DataTable', false);
    }

    public function test_manual_ordering_moves_products(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create();
        $a = Product::factory()->for($catalog)->create(['name' => 'A', 'position' => 0]);
        $b = Product::factory()->for($catalog)->create(['name' => 'B', 'position' => 1]);

        $this->patch("/products/{$b->id}/move", ['direction' => 'up'])->assertRedirect();

        $this->assertSame(['B', 'A'], $catalog->fresh()->products->pluck('name')->all());
        $this->patch("/products/{$b->id}/move", ['direction' => 'sideways'])->assertSessionHasErrors('direction');
    }

    public function test_new_product_is_appended_at_the_end(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create();
        Product::factory()->for($catalog)->create(['position' => 4]);
        $this->post('/products', ['catalog_id' => $catalog->id, 'name' => 'Nuevo', 'price' => 3]);
        $this->assertSame(5, Product::where('name', 'Nuevo')->value('position'));
    }

    public function test_dashboard_shows_real_user_data_and_chart(): void
    {
        $catalog = Catalog::factory()->for($this->user)->create(['name' => 'Mi Catalogo']);
        $category = Category::factory()->for($this->user)->create(['name' => 'Bebidas']);
        Product::factory()->count(2)->for($catalog)->create(['category_id' => $category->id]);
        Product::factory()->for($catalog)->create();
        // Datos de otro usuario que NO deben contarse
        Product::factory()->count(5)->for(Catalog::factory())->create();

        $this->get('/dashboard')->assertOk()
            ->assertSee($this->user->name)
            ->assertSee('Mi Catalogo')
            ->assertSee('id="categoryChart"', false)
            ->assertSee('chart.js', false)
            ->assertViewHas('stats', fn ($s) => $s['catalogs'] === 1 && $s['products'] === 3)
            ->assertViewHas('chart', fn ($c) => $c['labels'] === ['Bebidas', 'Sin categoría'] && $c['values'] === [2, 1]);
    }
}
