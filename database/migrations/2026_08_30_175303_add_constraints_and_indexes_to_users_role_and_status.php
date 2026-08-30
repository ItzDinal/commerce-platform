<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('status');
            $table->index(['role', 'status']);
        });

        // Only add MySQL CHECK constraints when using MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_role_check
                CHECK (role IN ('customer', 'admin', 'super_admin'))
            ");

            DB::statement("
                ALTER TABLE users
                ADD CONSTRAINT users_status_check
                CHECK (status IN ('active', 'inactive', 'suspended'))
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop MySQL CHECK constraints when using MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE users
                DROP CHECK users_role_check
            ");

            DB::statement("
                ALTER TABLE users
                DROP CHECK users_status_check
            ");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_status_index');
            $table->dropIndex('users_role_status_index');
        });
    }
};