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
            if (! Schema::hasColumn('contents', 'license_id')) {
                $table->foreignId('license_id')
                    ->nullable()
                    ->after('folder_id')
                    ->constrained('licenses')
                    ->nullOnDelete();

                $table->index(['license_id']);
            }
        });

        Schema::table('folders', function (Blueprint $table) {
            if (! Schema::hasColumn('folders', 'license_id')) {
                $table->foreignId('license_id')
                    ->nullable()
                    ->after('parent_id')
                    ->constrained('licenses')
                    ->nullOnDelete();

                $table->index(['license_id']);
            }

            if (! Schema::hasColumn('folders', 'allow_individual_sale')) {
                $table->boolean('allow_individual_sale')
                    ->default(true)
                    ->after('bundle_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            if (Schema::hasColumn('contents', 'license_id')) {
                $table->dropConstrainedForeignId('license_id');
            }
        });

        Schema::table('folders', function (Blueprint $table) {
            if (Schema::hasColumn('folders', 'license_id')) {
                $table->dropConstrainedForeignId('license_id');
            }

            if (Schema::hasColumn('folders', 'allow_individual_sale')) {
                $table->dropColumn('allow_individual_sale');
            }
        });
    }
};
