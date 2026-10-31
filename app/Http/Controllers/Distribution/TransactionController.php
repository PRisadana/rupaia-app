<?php

namespace App\Http\Controllers\Distribution;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Transaction::STATUSES)],
        ]);

        $transactions = Transaction::withCount('items')
            ->where('buyer_id', $user->id)
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
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
            'items.rating',
        ]);

        return view(
            'transactions.show',
            compact('transaction')
        );
    }
}
