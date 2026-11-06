<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Integración con la API pública DummyJSON (https://dummyjson.com/products) usando Laravel HTTP Client.
 * Aísla toda comunicación externa: ni los controladores ni las vistas Blade hacen peticiones HTTP externas.
 */
class DummyJsonService
{
    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.dummyjson.base_url'), '/'))
            ->acceptJson()
            ->timeout(10)
            ->retry(2, 200, throw: false);
    }

    /**
     * @return array{products: list<array<string,mixed>>, total: int, skip: int, limit: int}
     *
     * @throws \RuntimeException con mensaje en español si la API falla o no responde.
     */
    public function list(?string $search = null, int $page = 1, int $perPage = 12): array
    {
        $perPage = max(1, min($perPage, 30));
        $page = max(1, $page);
        $query = ['limit' => $perPage, 'skip' => ($page - 1) * $perPage];
        $path = 'products';

        if ($search !== null && trim($search) !== '') {
            $path = 'products/search';
            $query['q'] = trim($search);
        }

        $json = $this->get($path, $query);

        return [
            'products' => array_map([$this, 'normalize'], $json['products'] ?? []),
            'total' => (int) ($json['total'] ?? 0),
            'skip' => (int) ($json['skip'] ?? 0),
            'limit' => (int) ($json['limit'] ?? $perPage),
        ];
    }

    /** @return array<string,mixed>|null null si el producto no existe en la API externa */
    public function find(int $id): ?array
    {
        try {
            return $this->normalize($this->get("products/{$id}"));
        } catch (\RuntimeException $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    private function get(string $path, array $query = []): array
    {
        try {
            $response = $this->client()->get($path, $query);
        } catch (ConnectionException) {
            throw new \RuntimeException('No se pudo conectar con DummyJSON. Verifique su conexión a internet e intente de nuevo.');
        }

        if ($response->status() === 404) {
            throw new \RuntimeException('Producto no encontrado en DummyJSON.', 404);
        }

        try {
            $response->throw();
        } catch (RequestException) {
            throw new \RuntimeException("DummyJSON respondió con un error (HTTP {$response->status()}). Intente más tarde.", $response->status());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new \RuntimeException('DummyJSON devolvió una respuesta no válida.');
        }

        return $json;
    }

    /** Reduce la respuesta externa a los campos que usa Catalog Studio. */
    private function normalize(array $item): array
    {
        return [
            'id' => (int) ($item['id'] ?? 0),
            'title' => (string) ($item['title'] ?? 'Sin nombre'),
            'description' => (string) ($item['description'] ?? ''),
            'price' => (float) ($item['price'] ?? 0),
            'category' => (string) ($item['category'] ?? ''),
            'thumbnail' => $item['thumbnail'] ?? ($item['images'][0] ?? null),
        ];
    }
}
