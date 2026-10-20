<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Searchable;

class Tags extends Model
{
    use Searchable;

    protected $fillable = [
        'tag_name',
    ];

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'content_tags', 'tag_id', 'content_id');
    }
}
