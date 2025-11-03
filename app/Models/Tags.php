<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $table = 'tb_tag';

    protected $fillable = [
        'name_tag',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'tb_content_tag');
    }
}
