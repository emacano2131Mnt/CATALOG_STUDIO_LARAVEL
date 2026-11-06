<?php

namespace Tests\Feature;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CatalogDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DesignerAndPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Catalog $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
        $this->catalog = Catalog::factory()->for($this->user)->create(['name' => 'Mi Catálogo']);
        $this->actingAs($this->user);
    }

    public function test_designer_pages_render(): void
    {
        $this->get('/disenador')->assertOk()->assertSee('Mi Catálogo');
        $this->get("/disenador/{$this->catalog->id}")->assertOk()->assertSee('Minimalista')->assertSee('Premium')->assertSee('Comercial')->assertSee('Corporativa');
    }

    public function test_design_is_saved_per_catalog_and_resolved(): void
    {
        $this->put("/disenador/{$this->catalog->id}", ['design' => [
            'template' => 'premium', 'primary_color' => '#ff0000', 'columns' => 3, 'font' => 'serif', 'title' => 'Título X',
            'footer_text' => 'Pie', 'show_prices' => '1',
        ]])->assertRedirect();

        $design = CatalogDesign::resolve($this->catalog->fresh());
        $this->assertSame('premium', $design['template']);
        $this->assertSame('#ff0000', $design['primary_color']);
        $this->assertSame(3, $design['columns']);
        $this->assertTrue($design['show_prices']);
        $this->assertFalse($design['show_descriptions'], 'checkbox no enviado = oculto');
        $this->assertSame('Título X', $design['title']);
    }

    public function test_design_validation(): void
    {
        $this->put("/disenador/{$this->catalog->id}", ['design' => ['primary_color' => 'rojo', 'columns' => 9, 'template' => 'inexistente']])
            ->assertSessionHasErrors(['design.primary_color', 'design.columns', 'design.template']);
    }

    public function test_four_templates_exist(): void
    {
        $this->assertSame(['minimalista', 'premium', 'comercial', 'corporativa'], array_keys(CatalogDesign::templates()));
    }

    public function test_preview_applies_unsaved_design(): void
    {
        Product::factory()->for($this->catalog)->create(['name' => 'Camisa', 'price' => 10]);
        $this->get("/disenador/{$this->catalog->id}/vista-previa?design[primary_color]=%23123456&design[show_prices]=1&design[currency]=€")
            ->assertOk()->assertSee('#123456', false)->assertSee('Camisa')->assertSee('€10.00');
    }

    public function test_pdf_full_catalog_stream_and_download(): void
    {
        $category = Category::factory()->for($this->user)->create(['name' => 'Ropa']);
        Product::factory()->count(3)->for($this->catalog)->create(['category_id' => $category->id]);

        $view = $this->get("/catalogs/{$this->catalog->id}/pdf")->assertOk();
        $this->assertStringContainsString('application/pdf', $view->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $view->getContent());
        $this->assertStringContainsString('inline', $view->headers->get('content-disposition'));

        $download = $this->get("/catalogs/{$this->catalog->id}/pdf?download=1")->assertOk();
        $this->assertStringContainsString('attachment', $download->headers->get('content-disposition'));
        $this->assertStringContainsString('mi-catalogo.pdf', $download->headers->get('content-disposition'));
    }

    public function test_pdf_with_local_photo_renders(): void
    {
        // PNG 1x1 válido
        Storage::disk('public')->put('products/p.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        Product::factory()->for($this->catalog)->create(['image' => 'products/p.png']);

        $this->get("/catalogs/{$this->catalog->id}/pdf")->assertOk();
    }

    public function test_pdf_partial_export_of_selected_products(): void
    {
        $a = Product::factory()->for($this->catalog)->create(['name' => 'Incluido']);
        Product::factory()->for($this->catalog)->create(['name' => 'Excluido']);

        $response = $this->get("/catalogs/{$this->catalog->id}/pdf?products[]={$a->id}")->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // La selección parcial llega a la plantilla solo con los productos pedidos.
        $html = view('pdf.catalog', [
            'catalog' => $this->catalog, 'design' => CatalogDesign::resolve($this->catalog),
            'products' => $this->catalog->products()->whereKey($a->id)->get(), 'pdf' => true,
        ])->render();
        $this->assertStringContainsString('Incluido', $html);
        $this->assertStringNotContainsString('Excluido', $html);
    }

    public function test_pdf_rejects_products_from_another_catalog_even_if_same_owner(): void
    {
        $otherOwn = Product::factory()->for(Catalog::factory()->for($this->user))->create();
        $foreign = Product::factory()->for(Catalog::factory())->create();

        $this->get("/catalogs/{$this->catalog->id}/pdf?products[]={$otherOwn->id}")->assertSessionHasErrors('products.0');
        $this->get("/catalogs/{$this->catalog->id}/pdf?products[]={$foreign->id}")->assertSessionHasErrors('products.0');
    }

    public function test_products_report_pdf(): void
    {
        Product::factory()->count(2)->for($this->catalog)->create();
        $this->get('/informes')->assertOk();
        $response = $this->get('/informes/productos')->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertStringContainsString('attachment', $this->get('/informes/productos?download=1')->headers->get('content-disposition'));
    }

    public function test_products_report_rejects_foreign_catalog_filter(): void
    {
        $foreign = Catalog::factory()->create();
        $this->get('/informes/productos?catalog_id='.$foreign->id)->assertSessionHasErrors('catalog_id');
    }
}
