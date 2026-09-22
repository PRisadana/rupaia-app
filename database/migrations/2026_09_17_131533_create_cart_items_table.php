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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnDelete();

            $table->foreignId('content_id')
                ->nullable()
                ->constrained('contents')
                ->cascadeOnDelete();

            $table->foreignId('folder_id')
                ->nullable()
                ->constrained('folders')
                ->cascadeOnDelete();

            $table->foreignId('preset_id')
                ->nullable()
                ->constrained('presets')
                ->restrictOnDelete();

            $table->string('item_type');

            $table->decimal('price_snapshot', 15, 2);

            $table->timestamps();

            $table->unique([
                'cart_id',
                'content_id',
            ]);

            $table->unique([
                'cart_id',
                'folder_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
