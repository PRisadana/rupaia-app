<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShowcaseItem extends Model
{
    protected $fillable = [
        'seller_id',
        'content_id',
        'custom_path',
        'item_source',
        'description',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
