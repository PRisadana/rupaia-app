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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->string('content_title');
            $table->text('content_description')->nullable();
            $table->decimal('price', 12, 2)->default(0.00);
            $table->string('path_hi_res');
            $table->string('path_low_res');
            $table->string('visibility')->default('public'); // public, private, or by_request
            $table->string('status')->default('active'); // active, deleted, banned
            $table->timestamps();

            $table->index(['seller_id', 'folder_id']);
            $table->index(['visibility', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
