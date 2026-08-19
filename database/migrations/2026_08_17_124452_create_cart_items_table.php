<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('cart_id');
            $table->ulid('product_variant_id');

            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->foreign('cart_id')
                ->references('id')
                ->on('carts')
                ->cascadeOnDelete();

            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->cascadeOnDelete();

            $table->unique([
                'cart_id',
                'product_variant_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};