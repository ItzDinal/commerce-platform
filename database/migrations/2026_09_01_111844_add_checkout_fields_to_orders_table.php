<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->unique()->after('user_id');

            $table->ulid('shipping_address_id')
                ->nullable()
                ->after('order_number');

            $table->unsignedBigInteger('subtotal')->after('status');

            $table->unsignedBigInteger('shipping_fee')
                ->default(0)
                ->after('subtotal');

            $table->unsignedBigInteger('total')->after('shipping_fee');

            $table->foreign('shipping_address_id')
                ->references('id')
                ->on('addresses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);

            $table->dropForeign(['shipping_address_id']);

            $table->dropColumn([
                'order_number',
                'shipping_address_id',
                'subtotal',
                'shipping_fee',
                'total',
            ]);
        });
    }
};
