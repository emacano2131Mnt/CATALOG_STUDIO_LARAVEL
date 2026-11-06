<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración ADITIVA para la importación desde DummyJSON:
 *  - external_id: id del producto en la API externa (evita importar dos veces lo mismo).
 *  - image_url:   imagen remota (las subidas locales siguen usando la columna "image").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'external_id')) {
                $table->unsignedBigInteger('external_id')->nullable()->after('position');
            }
            if (! Schema::hasColumn('products', 'image_url')) {
                $table->string('image_url', 500)->nullable()->after('image');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['catalog_id', 'external_id'], 'products_catalog_external_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_catalog_external_unique');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'image_url']);
        });
    }
};
