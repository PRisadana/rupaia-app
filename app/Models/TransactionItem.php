<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'seller_id',
        'content_id',
        'folder_id',
        'preset_id',
        'payout_id',
        'license_id',

        'item_type',
        'item_name_snapshot',
        'license_name_snapshot',
        'license_terms_snapshot',
        'price_snapshot',

        'final_file_path',

        'commission_amount',
        'seller_amount',

        'download_available_at',
        'download_expires_at',
        'download_count',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',

        'download_available_at' => 'datetime',
        'download_expires_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
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

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
