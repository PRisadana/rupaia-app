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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->string('folder_name');
            $table->text('folder_description')->nullable();
            $table->string('visibility')->default('public'); // public, private, or by_request
            $table->boolean('is_bundle')->default(false);
            $table->decimal('bundle_price', 12, 2)->nullable();
            $table->string('status')->default('active'); // active, deleted, banned
            $table->timestamps();

            $table->index(['seller_id', 'parent_id']);
            $table->index(['visibility', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
