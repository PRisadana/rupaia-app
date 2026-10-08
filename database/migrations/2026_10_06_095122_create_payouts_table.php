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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('users');

            $table->foreignId('bank_account_id')
                ->constrained('bank_accounts');

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('status')
                ->default('pending');

            $table->timestamp('requested_at');

            $table->timestamp('processed_at')
                ->nullable();

            $table->text('admin_note')
                ->nullable();

            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
