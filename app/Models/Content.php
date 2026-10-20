<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\Searchable;

class Content extends Model
{
    use HasFactory;
    use Searchable;

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
        static::deleting(function (Content $content) {
            $stillReferenced = TransactionItem::query()
                ->where('final_file_path', $content->path_hi_res)
                ->exists();

            if (! $stillReferenced) {
                Storage::disk('local')->delete($content->path_hi_res);
            }

            Storage::disk('public')->delete($content->path_low_res);

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

    public function scopeCatalog($query)
    {
        return $query->where('visibility', 'public')
            ->where('status', 'active')
            ->where('sale_status', 'available');
    }

    public function scopeFilterCatalog($query, array $filters)
    {
        return $query
            ->when($filters['q'] ?? null, function ($query, $keyword) {
                $query->where(function ($group) use ($keyword) {
                    $group->search($keyword, ['content_title', 'content_description'])
                        ->orWhereHas('tags', fn($tag) => $tag->search($keyword, ['tag_name']));
                });
            })
            ->when($filters['tag'] ?? null, function ($query, $tagId) {
                $query->whereHas('tags', fn($tag) => $tag->where('tags.id', $tagId));
            })
            ->when($filters['license'] ?? null, fn($query, $id) => $query->where('license_id', $id))
            ->when($filters['seller'] ?? null, fn($query, $id) => $query->where('seller_id', $id))
            ->when($filters['sale_type'] ?? null, fn($query, $type) => $query->where('sale_type', $type))
            ->when(isset($filters['min_price']), fn($query) => $query->where('price', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn($query) => $query->where('price', '<=', $filters['max_price']));
    }
}
