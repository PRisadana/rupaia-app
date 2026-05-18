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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('content_id')->nullable()->constrained('contents')->onDelete('cascade');
            $table->foreignId('showcase_id')->nullable()->constrained('showcase_items')->onDelete('cascade');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullonDelete();

            $table->string('reason');
            $table->text('description')->nullable();

            $table->string('status')->default('pending');
            $table->string('action_taken')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['content_id', 'status']);
            $table->index(['showcase_id', 'status']);
            $table->index(['reporter_id', 'content_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
