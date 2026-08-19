<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('inventory_id');

            $table->string('type');
            $table->integer('quantity');
            $table->string('reason')->nullable();

            $table->timestamps();

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};