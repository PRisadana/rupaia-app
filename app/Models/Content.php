<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
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
        return $this->belongsTo(User::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'tb_content_tag');
    }
}
