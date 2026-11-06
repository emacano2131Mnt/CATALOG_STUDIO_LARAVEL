<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CatalogExportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignerController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ---------- Público ----------
Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('registro', [RegisterController::class, 'create'])->name('register');
    Route::post('registro', [RegisterController::class, 'store']);
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('recuperar-contrasena', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('recuperar-contrasena', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('restablecer-contrasena/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('restablecer-contrasena', [PasswordResetController::class, 'update'])->name('password.store');
});

// ---------- Protegido (auth) ----------
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('perfil/contrasena', [ProfileController::class, 'password'])->name('profile.password');
    Route::post('perfil/tokens', [ProfileController::class, 'createToken'])->name('profile.tokens.store');
    Route::delete('perfil/tokens/{token}', [ProfileController::class, 'deleteToken'])->whereNumber('token')->name('profile.tokens.destroy');

    Route::post('catalogs/{catalog}/duplicate', [CatalogController::class, 'duplicate'])->name('catalogs.duplicate');
    Route::get('catalogs/{catalog}/pdf', CatalogExportController::class)->name('catalogs.pdf');
    Route::resource('catalogs', CatalogController::class);

    Route::patch('products/{product}/move', [ProductController::class, 'move'])->name('products.move');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('disenador', [DesignerController::class, 'index'])->name('designer.index');
    Route::get('disenador/{catalog}', [DesignerController::class, 'edit'])->name('designer.edit');
    Route::put('disenador/{catalog}', [DesignerController::class, 'update'])->name('designer.update');
    Route::get('disenador/{catalog}/vista-previa', [DesignerController::class, 'preview'])->name('designer.preview');

    Route::get('importacion', [ImportController::class, 'index'])->name('import.index');
    Route::post('importacion', [ImportController::class, 'store'])->name('import.store');

    Route::get('informes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('informes/productos', [ReportController::class, 'products'])->name('reports.products');

    Route::view('api-consumo', 'api-consumer.index')->name('api.consumer');
});
