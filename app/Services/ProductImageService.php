<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Gestión segura de fotografías de productos con el Storage de Laravel.
 * - Disco "public" (storage/app/public/products), servido vía enlace simbólico public/storage.
 * - El nombre original del usuario NUNCA se usa: se genera un UUID y la extensión se deduce del MIME real.
 * - Solo se permiten extensiones de imagen raster (la validación MIME previa ya lo garantiza).
 */
class ProductImageService
{
    public const DISK = 'public';
    public const DIRECTORY = 'products';
    private const ALLOWED = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function store(UploadedFile $file): string
    {
        $extension = $file->guessExtension();
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if (! in_array($extension, self::ALLOWED, true)) {
            throw new \InvalidArgumentException('Tipo de imagen no permitido.');
        }

        $name = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs(self::DIRECTORY, $name, self::DISK);
    }

    /** Elimina un archivo solo si está dentro del directorio de productos (evita borrados arbitrarios). */
    public function delete(?string $path): void
    {
        if ($path && Str::startsWith($path, self::DIRECTORY.'/') && ! Str::contains($path, '..')) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /** Copia un archivo (usado al duplicar catálogos) y devuelve la nueva ruta. */
    public function copy(?string $path): ?string
    {
        if (! $path || ! Str::startsWith($path, self::DIRECTORY.'/') || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        $new = self::DIRECTORY.'/'.Str::uuid().'.'.pathinfo($path, PATHINFO_EXTENSION);
        Storage::disk(self::DISK)->copy($path, $new);

        return $new;
    }
}
