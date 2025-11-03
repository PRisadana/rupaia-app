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
        Schema::create('tb_content_tag', function (Blueprint $table) {
            $table->foreignId('id_content')->constrained('tb_content')->onDelete('cascade');
            $table->foreignId('id_tag')->constrained('tb_tag')->onDelete('cascade');
            // Menetapkan kedua kolom sebagai Primary Key
            // Ini mencegah duplikasi (satu foto tidak bisa punya tag 'Alam' 2x)
            $table->primary(['id_content', 'id_tag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_content_tag');
    }
};
