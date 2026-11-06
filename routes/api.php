<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
| API REST de Catalog Studio (prefijo /api). Autenticación: Laravel Sanctum
| (token Bearer personal, o cookie de sesión para llamadas del propio sitio).
*/
Route::middleware(['auth:sanctum', 'throttle:60,1'])->name('api.')->group(function () {
    Route::get('catalogs', [CatalogController::class, 'index'])->name('catalogs.index');
    Route::get('catalogs/{id}', [CatalogController::class, 'show'])->whereNumber('id')->name('catalogs.show');
    Route::get('catalogs/{id}/products', [CatalogController::class, 'products'])->whereNumber('id')->name('catalogs.products');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('products.show');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
});
