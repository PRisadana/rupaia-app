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
        Schema::table('contents', function (Blueprint $table) {
            $table->string('perceptual_hash', 128)
                ->nullable()
                ->after('status');

            $table->foreignId('similar_content_id')
                ->nullable()
                ->after('perceptual_hash')
                ->constrained('contents')
                ->nullOnDelete();

            $table->unsignedInteger('similarity_distance')
                ->nullable()
                ->after('similar_content_id');

            $table->decimal('moderation_score', 5, 4)
                ->nullable()
                ->after('similarity_distance');

            $table->string('moderation_category')
                ->nullable()
                ->after('moderation_score');

            $table->text('validation_reason')
                ->nullable()
                ->after('moderation_category');

            $table->timestamp('validated_at')
                ->nullable()
                ->after('validation_reason');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->after('validated_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable()
                ->after('reviewed_by');

            $table->text('review_note')
                ->nullable()
                ->after('reviewed_at');

            $table->index('perceptual_hash');
            $table->index(['status', 'validated_at']);
            $table->index(['similar_content_id', 'similarity_distance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['similar_content_id', 'similarity_distance']);
            $table->dropIndex(['status', 'validated_at']);
            $table->dropIndex(['perceptual_hash']);

            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['similar_content_id']);

            $table->dropColumn([
                'perceptual_hash',
                'similar_content_id',
                'similarity_distance',
                'moderation_score',
                'moderation_category',
                'validation_reason',
                'validated_at',
                'reviewed_by',
                'reviewed_at',
                'review_note',
            ]);
        });
    }
};
