<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('product_variant_id');

            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved')->default(0);

            $table->timestamps();

            $table->foreign('product_variant_id')
                ->references('id')
                ->on('product_variants')
                ->cascadeOnDelete();

            $table->unique('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};