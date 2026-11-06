<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 140);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('image', 255)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['catalog_id', 'position']);
            $table->index(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
