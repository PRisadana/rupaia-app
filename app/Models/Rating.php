<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rating extends Model
{

    use HasFactory, Searchable;

    public const VISIBLE = 'visible';
    public const HIDDEN = 'hidden';

    public const STATUSES = [self::VISIBLE, self::HIDDEN];

    protected $fillable = [
        'transaction_item_id',
        'rating_value',
        'comment',
        'is_name_masked',
        'status',
        'moderated_by',
        'moderated_at',
        'moderation_note',
    ];

    protected $casts = [
        'rating_value' => 'integer',
        'moderated_at' => 'datetime',
        'is_name_masked' => 'boolean'
    ];

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function reviewerName(): string
    {
        $name = $this->transactionItem->transaction->buyer->name ?? null;

        if ($name === null) {
            return 'Buyer';
        }

        return $this->is_name_masked ? self::maskName($name) : $name;
    }

    public static function maskName(string $name): string
    {
        return collect(preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY))
            ->map(function (string $word) {
                $length = mb_strlen($word);
                $keep   = (int) ceil($length / 2);

                return mb_substr($word, 0, $keep) . str_repeat('*', $length - $keep);
            })
            ->implode(' ');
    }

    public function scopeFilterAdmin($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status))
            ->when($filters['rating_value'] ?? null, fn($q, $value) => $q->where('rating_value', $value))
            ->when($filters['q'] ?? null, function ($q, $keyword) {
                $q->where(function ($group) use ($keyword) {
                    $group->search($keyword, ['comment'])
                        ->orWhereHas('transactionItem', function ($item) use ($keyword) {
                            $item->search($keyword, ['item_name_snapshot'])
                                ->orWhereHas('seller', fn($seller) => $seller->search($keyword, ['name']))
                                ->orWhereHas('transaction.buyer', fn($buyer) => $buyer->search($keyword, ['name', 'email']));
                        });
                });
            });
    }
}
