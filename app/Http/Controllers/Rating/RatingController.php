<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Transaction $transaction, TransactionItem $transactionItem)
    {
        abort_unless($transaction->buyer_id === $request->user()->id, 403);

        abort_unless($transactionItem->transaction_id === $transaction->id, 404);

        if ($transaction->status !== 'completed' || $transaction->payment_status !== 'success') {
            return back()->withErrors([
                'rating' => 'You can only rate items from completed transactions.',
            ]);
        }

        $existing = $transactionItem->rating;

        if ($existing && $existing->status === Rating::HIDDEN) {
            return back()->withErrors([
                'rating' => 'This rating was hidden by an admin and can no longer be edited.',
            ]);
        }

        $validated = $request->validate([
            'rating_value' => ['required', 'integer', 'between:1,5'],
            'comment'      => ['nullable', 'string', 'max:1000'],
        ], [], [
            'rating_value' => 'rating',
            'is_name_masked' => ['nullable', 'boolean'],
        ]);

        Rating::updateOrCreate(
            ['transaction_item_id' => $transactionItem->id],
            [
                'rating_value' => $validated['rating_value'],
                'comment'      => $validated['comment'] ?? null,
                'is_name_masked' => $request->boolean('is_name_masked'),
            ]
        );

        return back()->with(
            'success',
            $existing ? 'Your rating has been updated.' : 'Thank you! Your rating has been saved.'
        );
    }
}
