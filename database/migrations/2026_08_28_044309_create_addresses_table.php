<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('user_id');

            $table->string('label')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();

            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();

            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');

            $table->string('phone')->nullable();

            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};