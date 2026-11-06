<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Services\DummyJsonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Sección "Importar productos": consulta DummyJSON (vía servicio) e importa a un catálogo propio. */
class ImportController extends Controller
{
    public function __construct(private DummyJsonService $dummyJson)
    {
    }

    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $page = (int) ($data['page'] ?? 1);
        $perPage = 12;
        $result = null;
        $error = null;

        try {
            $result = $this->dummyJson->list($data['q'] ?? null, $page, $perPage);
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        $catalogs = Catalog::ownedBy($request->user())->orderBy('name')->get();

        // Ids ya importados por este usuario, para marcar duplicados en la interfaz.
        $imported = $result
            ? Product::ownedBy($request->user())->whereIn('external_id', collect($result['products'])->pluck('id'))
                ->get(['catalog_id', 'external_id'])->groupBy('external_id')->map(fn ($g) => $g->pluck('catalog_id')->all())
            : collect();

        return view('import.index', [
            'result' => $result,
            'error' => $error,
            'catalogs' => $catalogs,
            'imported' => $imported,
            'q' => $data['q'] ?? '',
            'page' => $page,
            'pages' => $result ? (int) ceil($result['total'] / $perPage) : 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'catalog_id' => ['required', 'integer', 'exists:catalogs,id'],
            'ids' => ['required', 'array', 'min:1', 'max:30'],
            'ids.*' => ['integer', 'min:1'],
        ], ['ids.required' => 'Seleccione al menos un producto para importar.'], ['catalog_id' => 'catálogo', 'ids' => 'productos']);

        $catalog = Catalog::findOrFail($data['catalog_id']);
        $this->authorize('update', $catalog);

        $imported = $skipped = $failed = 0;
        $nextPosition = (int) Product::where('catalog_id', $catalog->id)->max('position') + 1;

        foreach (array_unique($data['ids']) as $id) {
            // Evita duplicados: mismo producto externo en el mismo catálogo.
            if (Product::where('catalog_id', $catalog->id)->where('external_id', $id)->exists()) {
                $skipped++;
                continue;
            }

            try {
                // Se consulta de nuevo a la API: no se confía en datos enviados por el formulario.
                $item = $this->dummyJson->find((int) $id);
            } catch (\RuntimeException) {
                $failed++;
                continue;
            }

            if (! $item) {
                $failed++;
                continue;
            }

            $category = null;
            if ($item['category'] !== '') {
                $name = Str::of($item['category'])->replace('-', ' ')->title()->limit(80, '')->toString();
                $category = Category::firstOrCreate(['user_id' => $user->id, 'name' => $name]);
            }

            Product::create([
                'catalog_id' => $catalog->id,
                'category_id' => $category?->id,
                'name' => Str::limit($item['title'], 140, ''),
                'description' => $item['description'],
                'price' => $item['price'],
                'image_url' => $item['thumbnail'],
                'external_id' => $item['id'],
                'position' => $nextPosition++,
            ]);
            $imported++;
        }

        $message = "Importación finalizada: {$imported} importado(s), {$skipped} omitido(s) por duplicado";
        $message .= $failed ? ", {$failed} con error de conexión o inexistente(s)." : '.';

        return to_route('import.index')->with($imported > 0 ? 'status' : 'error', $message);
    }
}
