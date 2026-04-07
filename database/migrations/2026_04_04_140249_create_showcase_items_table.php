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
        Schema::create('showcase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('content_id')->nullable()->constrained('contents')->onDelete('cascade');
            $table->string('custom_path')->nullable();
            $table->string('item_source')->default('custom'); // custom or content
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, deleted, banned
            $table->timestamps();

            $table->index(['seller_id', 'content_id']);
            $table->index(['item_source', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showcase_items');
    }
};
