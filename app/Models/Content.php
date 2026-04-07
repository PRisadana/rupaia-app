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
        'content_title',
        'content_description',
        'price',
        'path_hi_res',
        'path_low_res',
        'visibility',
        'status'
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
}
