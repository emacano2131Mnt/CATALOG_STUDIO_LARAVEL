<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración ADITIVA. Las categorías pasan a tener propietario para que cada usuario
 * administre las suyas. Las categorías ya existentes quedan con user_id = NULL y se
 * consideran "globales": todos las ven y las pueden usar, pero nadie las edita/elimina.
 * No borra ni modifica filas existentes. Es idempotente (comprueba antes de alterar).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'user_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        // El nombre único global impediría que dos usuarios tengan una categoría con el mismo nombre.
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_unique');
            $table->unique(['user_id', 'name'], 'categories_user_id_name_unique');
        });
    }

    public function down(): void
    {
        // Orden obligatorio en MySQL: la FK usa el índice compuesto, así que se elimina primero la FK.
        // OJO: si dos usuarios ya tienen categorías con el mismo nombre, restaurar unique(name) fallará.
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_user_id_name_unique');
            $table->dropColumn('user_id');
            $table->unique('name', 'categories_name_unique');
        });
    }
};
