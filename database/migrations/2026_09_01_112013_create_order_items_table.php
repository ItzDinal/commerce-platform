<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('order_id');

            $table->ulid('product_variant_id');

            // Product information snapshot at the time of purchase.
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku');

            $table->unsignedInteger('quantity');

            // All monetary values are stored as integer LKR.
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('line_total');

            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};