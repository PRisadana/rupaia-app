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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->constrained('transactions')
                ->cascadeOnDelete();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('content_id')
                ->nullable()
                ->constrained('contents')
                ->nullOnDelete();

            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->nullOnDelete();

            $table->foreignId('preset_id')
                ->nullable()
                ->constrained('presets')
                ->restrictOnDelete();

            $table->unsignedBigInteger('payout_id')->nullable();

            $table->foreignId('license_id')
                ->constrained('licenses')
                ->restrictOnDelete();

            $table->string('item_type');
            $table->string('item_name_snapshot');
            $table->string('license_name_snapshot');
            $table->text('license_terms_snapshot');
            $table->decimal('price_snapshot', 15, 2);
            $table->string('final_file_path')->nullable();
            $table->decimal('commission_amount', 15, 2)->nullable();
            $table->decimal('seller_amount', 15, 2)->nullable();
            $table->timestamp('download_available_at')->nullable();
            $table->timestamp('download_expires_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
