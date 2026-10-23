<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

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

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function scopeSuccessfulSalesOf($query, int $sellerId)
    {
        return $query
            ->where('seller_id', $sellerId)
            ->whereNotNull('seller_amount')
            ->whereHas('transaction', function ($transaction) {
                $transaction
                    ->where('status', 'completed')
                    ->where('payment_status', 'success');
            });
    }

    public function scopePaidSince($query, ?Carbon $start)
    {
        return $query->when($start, function ($q) use ($start) {
            $q->whereHas('transaction', fn($t) => $t->where('paid_at', '>=', $start));
        });
    }

    public function scopeInPendingTransaction($query)
    {
        return $query->whereHas('transaction', function ($transactionQuery) {
            $transactionQuery->where(function ($statusQuery) {
                $statusQuery->where('status', 'processing')
                    ->orWhere(function ($pendingQuery) {
                        $pendingQuery->where('status', 'pending')
                            ->where(
                                'created_at',
                                '>=',
                                now()->subMinutes((int) config('marketplace.pending_hold_minutes'))
                            );
                    });
            });
        });
    }

    public function scopeHoldingContent($query, $contentIds)
    {
        return $query
            ->whereIn('content_id', Arr::wrap($contentIds))
            ->inPendingTransaction();
    }

    public function scopeHoldingFolder($query, $folderIds)
    {
        return $query
            ->whereIn('folder_id', Arr::wrap($folderIds))
            ->inPendingTransaction();
    }

    public function scopeActivePurchaseOf($query, int $buyerId)
    {
        $holdMinutes = (int) config('marketplace.pending_hold_minutes', 60);

        return $query
            ->whereHas('transaction', function ($transaction) use ($buyerId, $holdMinutes) {
                $transaction->where('buyer_id', $buyerId)
                    ->where(function ($status) use ($holdMinutes) {
                        $status->where('status', 'processing')
                            ->orWhere('status', 'completed')
                            ->orWhere(function ($pending) use ($holdMinutes) {
                                $pending->where('status', 'pending')
                                    ->where('created_at', '>=', now()->subMinutes($holdMinutes));
                            });
                    });
            })
            ->where(function ($item) {
                $item->whereNull('download_expires_at')
                    ->orWhere('download_expires_at', '>', now());
            });
    }
}
