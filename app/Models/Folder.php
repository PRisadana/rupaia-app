<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    // protected $table = 'tb_folder';

    protected $fillable = [
        'seller_id',
        'parent_id',
        'folder_name',
        'folder_description',
        'visibility',
        'is_bundle',
        'bundle_price',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'folder_id');
    }

    public function updateVisibilityRecursive(string $visibility): void
    {
        // Update Visibilitas Folder ini
        $this->visibility = $visibility;
        $this->save();

        // Update semua konten di folder ini
        $this->contents()->update(['visibility' => $visibility,]);

        // Rekursif ke seluruh subfolder
        foreach ($this->children as $child) {
            $child->updateVisibilityRecursive($visibility);
        }
    }
}
