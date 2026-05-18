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
        Schema::table('contents', function (Blueprint $table) {
            $table->string('sale_type')
                ->default('multi_sale')
                ->after('price');

            $table->string('sale_status')
                ->default('available')
                ->after('sale_type');

            $table->index(['sale_type', 'sale_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['sale_type', 'sale_status']);
            $table->dropColumn(['sale_type', 'sale_status']);
        });
    }
};
