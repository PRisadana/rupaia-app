<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'content_id',
        'folder_id',
        'preset_id',
        'item_type',
        'price_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function preset()
    {
        return $this->belongsTo(Preset::class);
    }
}
