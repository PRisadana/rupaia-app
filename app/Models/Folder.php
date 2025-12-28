<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $table = 'tb_folder';

    protected $fillable = [
        'id_users',
        'id_parent',
        'folder_name',
        'folder_description',
        'visibility_folder'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'id_parent');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'id_parent');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'id_folder', 'id');
    }

    public function updateVisibilityRecursive(string $visibility): void
    {
        // Update Visibilitas Folder ini
        $this->visibility_folder = $visibility;
        $this->save();

        // Update semua konten di folder ini
        $this->contents()->update(['visibility_content' => $visibility,]);

        // Rekursif ke seluruh subfolder
        foreach ($this->children as $child) {
            $child->updateVisibilityRecursive($visibility);
        }
    }
}
