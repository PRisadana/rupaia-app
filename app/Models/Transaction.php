<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\Searchable;

class Transaction extends Model
{
    use HasFactory, Searchable;

    public const STATUSES = ['pending', 'processing', 'completed', 'cancelled'];
    public const PAYMENT_STATUSES = ['pending', 'success', 'failed', 'expired'];

    protected $fillable = [
        'buyer_id',
        'order_code',
        'status',
        'total_amount',
        'snap_token',
        'payment_status',
        'paid_at',
        'invoice_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeFilterAdmin($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn($q, $status) => $q->where('payment_status', $status))
            ->when($filters['q'] ?? null, function ($q, $keyword) {
                $q->where(function ($group) use ($keyword) {
                    $group->search($keyword, ['order_code', 'invoice_number'])
                        ->orWhereHas('buyer', fn($buyer) => $buyer->search($keyword, ['name', 'email']));
                });
            });
    }
}
