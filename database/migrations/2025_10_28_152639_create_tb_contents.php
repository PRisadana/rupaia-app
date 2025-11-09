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
        Schema::create('tb_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_users')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_folder')->constrained('tb_folder')->onDelete('cascade');

            $table->string('content_title');
            $table->text('content_description')->nullable();

            $table->string('path_hi_res');
            $table->string('path_low_res');

            $table->enum('visibility_content', ['public', 'private', 'by_request'])->default('public');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_content');
    }
};
