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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->hasMany(Folder::class);
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'id_parent');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'id_parent');
    }
}
