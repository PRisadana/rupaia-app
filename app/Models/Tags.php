<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    // protected $table = 'tb_tag';

    protected $fillable = [
        'tag_name',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'content_tags', 'content_id', 'tag_id');
    }
}
