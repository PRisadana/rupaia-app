<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('licences') && ! Schema::hasTable('licenses')) {
            Schema::rename('licences', 'licenses');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('licenses') && ! Schema::hasTable('licences')) {
            Schema::rename('licenses', 'licences');
        }
    }
};
