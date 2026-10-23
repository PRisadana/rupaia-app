<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\SellerAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerSalesController extends Controller
{
    public function index(Request $request, SellerAnalyticsService $analyticsService)
    {
        $sellerId = Auth::id();

        $period = $analyticsService->resolvePeriod($request->query('period'));

        $successfulItemsQuery = TransactionItem::successfulSalesOf($sellerId);

        $totalRevenue     = (clone $successfulItemsQuery)->sum('seller_amount');
        $availableBalance = (clone $successfulItemsQuery)->whereNull('payout_id')->sum('seller_amount');
        $totalItemsSold   = (clone $successfulItemsQuery)->count();
        $totalContentsSold = (clone $successfulItemsQuery)->where('item_type', 'content')->count();
        $totalBundlesSold  = (clone $successfulItemsQuery)->where('item_type', 'bundle')->count();

        $analytics = $analyticsService->summarize($sellerId, $period);
        $periods   = SellerAnalyticsService::PERIODS;

        $sales = TransactionItem::successfulSalesOf($sellerId)
            ->paidSince($analyticsService->startDate($period))
            ->with(['transaction', 'content', 'folder', 'license', 'payout'])
            ->orderByDesc(
                Transaction::select('paid_at')
                    ->whereColumn('transactions.id', 'transaction_items.transaction_id')
                    ->limit(1)
            )
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.sales.index', compact(
            'sales',
            'analytics',
            'periods',
            'totalRevenue',
            'availableBalance',
            'totalItemsSold',
            'totalContentsSold',
            'totalBundlesSold'
        ));
    }
}
