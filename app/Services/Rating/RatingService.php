<?php

namespace App\Services\Rating;

use App\Models\Rating;
use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RatingService
{
    public const REVIEW_LIMIT = 10;

    public function summaryForContent(int $contentId): array
    {
        $query = Rating::query()->whereHas('transactionItem', function ($item) use ($contentId) {
            $item->where('content_id', $contentId)
                ->whereIn('item_type', ['content', 'content_with_preset']);
        });

        return $this->summarize($query);
    }

    public function summaryForBundle(int $folderId): array
    {
        $query = Rating::query()->whereHas('transactionItem', function ($item) use ($folderId) {
            $item->where('folder_id', $folderId)
                ->where('item_type', 'bundle');
        });

        return $this->summarize($query);
    }

    public function sellerOverview(int $sellerId): array
    {
        $ratings = Rating::query()
            ->whereHas('transactionItem', fn($item) => $item->where('seller_id', $sellerId))
            ->with('transactionItem.transaction')
            ->get();

        $groups  = $ratings->groupBy(fn(Rating $rating) => $this->productKey($rating->transactionItem));
        $counted = $groups->map(fn(Collection $group) => $this->latestVisiblePerBuyer($group));

        $products = $groups
            ->map(function (Collection $group, string $key) use ($counted) {
                $valid = $counted[$key];
                $item  = $group->first()->transactionItem;

                return [
                    'id'      => $item->item_type === 'bundle' ? $item->folder_id : $item->content_id,
                    'name'    => $item->item_name_snapshot,
                    'type'    => $item->item_type === 'bundle' ? 'bundle' : 'content',
                    'average' => $valid->isEmpty() ? null : round($valid->avg('rating_value'), 1),
                    'count'   => $valid->count(),
                    'hidden'  => $group->where('status', Rating::HIDDEN)->count(),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $all = $counted->collapse();

        return [
            'average'  => $all->isEmpty() ? null : round($all->avg('rating_value'), 1),
            'count'    => $all->count(),
            'products' => $products,
        ];
    }

    private function summarize(Builder $query): array
    {
        $counted = $this->latestVisiblePerBuyer(
            $query->with('transactionItem.transaction.buyer:id,name')->get()
        );

        return [
            'average' => $counted->isEmpty() ? null : round($counted->avg('rating_value'), 1),
            'count'   => $counted->count(),
            'reviews' => $counted
                ->filter(fn(Rating $rating) => filled($rating->comment))
                ->sortByDesc('updated_at')
                ->take(self::REVIEW_LIMIT)
                ->values(),
        ];
    }

    private function latestVisiblePerBuyer(Collection $ratings): Collection
    {
        return $ratings
            ->groupBy(fn(Rating $rating) => $rating->transactionItem->transaction->buyer_id)
            ->map(fn(Collection $group) => $group->sortBy([['updated_at', 'desc'], ['id', 'desc']])->first())
            ->filter(fn(Rating $rating) => $rating->status === Rating::VISIBLE)
            ->values();
    }

    private function productKey(TransactionItem $item): string
    {
        return $item->item_type === 'bundle'
            ? 'bundle:' . ($item->folder_id ?? $item->item_name_snapshot)
            : 'content:' . ($item->content_id ?? $item->item_name_snapshot);
    }
}
