<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Content extends Model
{
    use HasFactory;

    protected $table = 'tb_content';

    protected $fillable = [
        'id_users',
        'id_folder',
        'content_title',
        'content_description',
        'path_hi_res',
        'path_low_res',
        'visibility_content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_users', 'id');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class, 'id_folder');
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'tb_content_tag', 'id_content', 'id_tag', 'id', 'id');
    }

    protected static function booted(): void
    {
        // Ini akan berjalan OTOMATIS setiap kali $content->delete() dipanggil
        static::deleting(function (Content $content) {
            Storage::disk('public')->delete([$content->path_hi_res, $content->path_low_res]);

            // Hapus relasi tags ketika konten dihapus
            $content->tags()->detach();
        });
    }
}
