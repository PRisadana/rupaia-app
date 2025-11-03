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
        Schema::create('tb_folder', function (Blueprint $table) {
            $table->id();

            // Kolom Foreign Key (FK) ke tabel 'users'
            // Ini adalah "Pemilik" folder
            $table->foreignId('id_users')->constrained('users')->onDelete('cascade');

            // Kolom Foreign Key (FK) ke tabel 'folders' (diri sendiri)
            // Ini adalah "Induk" dari folder (untuk folder bertingkat)
            $table->foreignId('id_parent')->nullable()->constrained('tb_folder')->onDelete('cascade');

            $table->string('folder_name');
            $table->text('folder_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_folders');
    }
};
