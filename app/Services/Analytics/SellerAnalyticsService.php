<?php

namespace App\Services\Analytics;

use App\Models\TransactionItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class SellerAnalyticsService
{
    public const DEFAULT_PERIOD = '30d';

    public const PERIODS = [
        '7d'   => 'Last 7 days',
        '30d'  => 'Last 30 days',
        '90d'  => 'Last 90 days',
        'year' => 'This year',
        'all'  => 'All time',
    ];

    private const DAILY_PERIODS = ['7d', '30d', '90d'];

    public function resolvePeriod(?string $requested): string
    {
        return array_key_exists((string) $requested, self::PERIODS)
            ? $requested
            : self::DEFAULT_PERIOD;
    }

    public function startDate(string $period): ?Carbon
    {
        return match ($period) {
            '7d'    => now()->subDays(6)->startOfDay(),
            '30d'   => now()->subDays(29)->startOfDay(),
            '90d'   => now()->subDays(89)->startOfDay(),
            'year'  => now()->startOfYear(),
            default => null,
        };
    }

    public function summarize(int $sellerId, string $period): array
    {
        $start = $this->startDate($period);

        $items = TransactionItem::successfulSalesOf($sellerId)
            ->paidSince($start)
            ->with('transaction:id,paid_at')
            ->get([
                'id',
                'transaction_id',
                'content_id',
                'folder_id',
                'item_type',
                'item_name_snapshot',
                'seller_amount',
            ]);

        return [
            'period'       => $period,
            'label'        => self::PERIODS[$period],
            'totalRevenue' => $items->sum('seller_amount'),
            'totalSold'    => $items->count(),
            'contentSold'  => $items->where('item_type', 'content')->count(),
            'bundleSold'   => $items->where('item_type', 'bundle')->count(),
            'bestSellers'  => $this->bestSellers($items),
            'trend'        => $this->trend($items, $period, $start),
        ];
    }

    private function bestSellers(Collection $items): Collection
    {
        return $items
            ->groupBy(fn($item) => $item->item_type . ':' . ($item->content_id ?? $item->folder_id ?? $item->item_name_snapshot))
            ->map(fn($group) => [
                'name'    => $group->first()->item_name_snapshot,
                'type'    => $group->first()->item_type,
                'units'   => $group->count(),
                'revenue' => $group->sum('seller_amount'),
            ])
            ->sortBy([['units', 'desc'], ['revenue', 'desc']])
            ->take(5)
            ->values();
    }

    private function trend(Collection $items, string $period, ?Carbon $start): array
    {
        $daily  = in_array($period, self::DAILY_PERIODS, true);
        $format = $daily ? 'Y-m-d' : 'Y-m';

        $dated   = $items->filter(fn($item) => $item->transaction?->paid_at);
        $grouped = $dated->groupBy(fn($item) => $item->transaction->paid_at->format($format));

        $from = $start ?? ($dated->isEmpty()
            ? now()->startOfMonth()
            : $dated->map(fn($item) => $item->transaction->paid_at)->sort()->first()->copy()->startOfMonth());

        $labels = $revenue = $units = [];

        foreach (CarbonPeriod::create($from->copy()->startOfDay(), $daily ? '1 day' : '1 month', now()) as $point) {
            $bucket = $grouped->get($point->format($format), collect());

            $labels[]  = $daily ? $point->format('d M') : $point->format('M Y');
            $revenue[] = (float) $bucket->sum('seller_amount');
            $units[]   = $bucket->count();
        }

        return compact('labels', 'revenue', 'units');
    }
}
