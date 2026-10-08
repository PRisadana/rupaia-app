<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SellerPayoutController extends Controller
{
    public function index()
    {
        $sellerId = Auth::id();

        $availableTransactions = Transaction::query()
            ->where('status', 'completed')
            ->where('payment_status', 'success')
            ->whereHas('items', function ($query) use ($sellerId) {
                $query
                    ->where('seller_id', $sellerId)
                    ->whereNotNull('seller_amount')
                    ->whereNull('payout_id');
            })
            ->with([
                'items' => function ($query) use ($sellerId) {
                    $query
                        ->where('seller_id', $sellerId)
                        ->whereNotNull('seller_amount')
                        ->whereNull('payout_id');
                },
            ])
            ->orderByDesc('paid_at')
            ->paginate(
                10,
                ['*'],
                'sales_page'
            );

        $availableBalance = TransactionItem::query()
            ->where('seller_id', $sellerId)
            ->whereNotNull('seller_amount')
            ->whereNull('payout_id')
            ->whereHas('transaction', function ($query) {
                $query
                    ->where('status', 'completed')
                    ->where('payment_status', 'success');
            })
            ->sum('seller_amount');

        $bankAccounts = BankAccount::query()
            ->where('seller_id', $sellerId)
            ->orderByDesc('is_default')
            ->orderBy('bank_name')
            ->get();

        $payouts = Payout::query()
            ->where('seller_id', $sellerId)
            ->with([
                'bankAccount',
                'items.transaction',
            ])
            ->orderByDesc('requested_at')
            ->paginate(
                10,
                ['*'],
                'history_page'
            );

        return view(
            'dashboard.payout.index',
            compact(
                'availableTransactions',
                'availableBalance',
                'bankAccounts',
                'payouts'
            )
        );
    }

    public function store(Request $request)
    {
        $sellerId = Auth::id();

        $validated = $request->validate([
            'bank_account_id' => [
                'required',
                'integer',
            ],
            'transaction_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'transaction_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],
        ]);

        $bankAccount = BankAccount::query()
            ->where('seller_id', $sellerId)
            ->whereKey($validated['bank_account_id'])
            ->first();

        if (! $bankAccount) {
            throw ValidationException::withMessages([
                'bank_account_id'
                => 'The selected bank account is not valid.',
            ]);
        }

        $transactionIds = collect($validated['transaction_ids'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        DB::transaction(
            function () use (
                $sellerId,
                $transactionIds,
                $bankAccount
            ) {
                $items = TransactionItem::query()
                    ->where('seller_id', $sellerId)
                    ->whereIn(
                        'transaction_id',
                        $transactionIds
                    )
                    ->whereNotNull('seller_amount')
                    ->whereNull('payout_id')
                    ->whereHas(
                        'transaction',
                        function ($query) {
                            $query
                                ->where(
                                    'status',
                                    'completed'
                                )
                                ->where(
                                    'payment_status',
                                    'success'
                                );
                        }
                    )
                    ->lockForUpdate()
                    ->get();

                $eligibleTransactionIds = $items
                    ->pluck('transaction_id')
                    ->unique()
                    ->values();

                $invalidTransactionIds =
                    $transactionIds->diff(
                        $eligibleTransactionIds
                    );

                if ($items->isEmpty() || $invalidTransactionIds->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'transaction_ids'
                        => 'One or more selected transactions are no longer available for payout.',
                    ]);
                }

                $amount = round(
                    (float) $items->sum('seller_amount'),
                    2
                );

                if ($amount <= 0) {
                    throw ValidationException::withMessages([
                        'transaction_ids'
                        => 'The selected transactions do not have an available payout balance.',
                    ]);
                }

                $payout = Payout::create([
                    'seller_id' => $sellerId,
                    'bank_account_id' => $bankAccount->id,
                    'processed_by' => null,
                    'amount' => $amount,
                    'status' => 'pending',
                    'requested_at' => now(),
                    'processed_at' => null,
                    'admin_note' => null,
                ]);

                TransactionItem::query()
                    ->whereIn(
                        'id',
                        $items->pluck('id')
                    )
                    ->update(['payout_id' => $payout->id,]);
            }
        );

        return redirect()
            ->route('seller.payout.index')
            ->with(
                'success',
                'Payout request submitted successfully.'
            );
    }
}
