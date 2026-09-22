<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! in_array($user->role, ['buyer', 'seller'], true)) {
            abort(403);
        }

        $transactions = Transaction::withCount('items')
            ->where('buyer_id', $user->id)
            ->latest()
            ->paginate(10);

        return view(
            'transactions.index',
            compact('transactions')
        );
    }

    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id) {
            abort(403);
        }

        $transaction->load([
            'items.seller',
            'items.content',
            'items.folder',
            'items.preset',
            'items.license',
        ]);

        return view(
            'transactions.show',
            compact('transaction')
        );
    }
}
