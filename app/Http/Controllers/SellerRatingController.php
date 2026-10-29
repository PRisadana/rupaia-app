<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Services\Rating\RatingService;
use Illuminate\Support\Facades\Auth;

class SellerRatingController extends Controller
{
    public function index(RatingService $ratingService)
    {
        $sellerId = Auth::id();

        $overview = $ratingService->sellerOverview($sellerId);

        $reviews = Rating::query()
            ->whereHas('transactionItem', fn($item) => $item->where('seller_id', $sellerId))
            ->with('transactionItem.transaction.buyer:id,name')
            ->with('transactionItem.content')
            ->with('transactionItem.folder')
            ->latest('updated_at')
            ->paginate(10);

        return view('dashboard.rating.index', compact('overview', 'reviews'));
    }
}
