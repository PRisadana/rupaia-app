<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Content extends Model
{
    use HasFactory;

    // protected $table = 'tb_content';

    protected $fillable = [
        'seller_id',
        'folder_id',
        'license_id',

        'content_title',
        'content_description',
        'price',
        'sale_type',
        'sale_status',

        'path_hi_res',
        'path_low_res',
        'visibility',
        'status',

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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'moderation_score' => 'decimal:4',
        'validated_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'content_tags', 'content_id', 'tag_id');
    }

    protected static function booted(): void
    {
        // berjalan OTOMATIS setiap kali $content->delete() dipanggil
        static::deleting(function (Content $content) {
            Storage::disk('public')->delete([$content->path_hi_res, $content->path_low_res]);

            // Hapus relasi tags ketika konten dihapus
            $content->tags()->detach();
        });
    }

    public function showcaseItems()
    {
        return $this->hasMany(ShowcaseItem::class, 'content_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'content_id');
    }

    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    public function similarContent()
    {
        return $this->belongsTo(Content::class, 'similar_content_id');
    }

    public function similarContents()
    {
        return $this->hasMany(Content::class, 'similar_content_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
