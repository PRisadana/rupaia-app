<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Payout;
use Illuminate\Support\Facades\Auth;

class SellerSalesController extends Controller
{
    public function index()
    {
        $sellerId = Auth::id();

        $successfulItemsQuery = TransactionItem::query()
            ->where('seller_id', $sellerId)
            ->whereNotNull('seller_amount')
            ->whereHas('transaction', function ($query) {
                $query
                    ->where('status', 'completed')
                    ->where('payment_status', 'success');
            });

        $totalRevenue = (clone $successfulItemsQuery)->sum('seller_amount');

        $availableBalance = (clone $successfulItemsQuery)
            ->whereNull('payout_id')
            ->sum('seller_amount');

        $totalItemsSold = (clone $successfulItemsQuery)->count();
        $totalContentsSold = (clone $successfulItemsQuery)
            ->where('item_type', 'content')
            ->count();
        $totalBundlesSold = (clone $successfulItemsQuery)
            ->where('item_type', 'bundle')
            ->count();

        $sales = (clone $successfulItemsQuery)
            ->with([
                'transaction',
                'content',
                'folder',
                'license',
                'payout',
            ])
            ->orderByDesc(
                Transaction::select('paid_at')
                    ->whereColumn(
                        'transactions.id',
                        'transaction_items.transaction_id'
                    )
                    ->limit(1)
            )
            ->paginate(15);

        return view(
            'dashboard.sales.index',
            compact(
                'sales',
                'totalRevenue',
                'availableBalance',
                'totalItemsSold',
                'totalContentsSold',
                'totalBundlesSold'
            )
        );
    }
}
