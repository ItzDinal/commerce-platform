<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_attribute', function (Blueprint $table) {
            $table->ulid('category_id');
            $table->ulid('product_attribute_id');

            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary([
                'category_id',
                'product_attribute_id',
            ]);

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();

            $table->foreign('product_attribute_id')
                ->references('id')
                ->on('product_attributes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attribute');
    }
};