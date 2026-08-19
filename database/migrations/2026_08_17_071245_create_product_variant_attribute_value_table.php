<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variant_attribute_value', function (Blueprint $table) {
            $table->ulid('product_variant_id');
            $table->ulid('product_attribute_value_id');

            $table->primary([
                'product_variant_id',
                'product_attribute_value_id',
            ]);

            $table->foreign(
                'product_variant_id',
                'pvav_variant_fk'
            )
                ->references('id')
                ->on('product_variants')
                ->cascadeOnDelete();

            $table->foreign(
                'product_attribute_value_id',
                'pvav_value_fk'
            )
                ->references('id')
                ->on('product_attribute_values')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_attribute_value');
    }
};