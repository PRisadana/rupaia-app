<?php

namespace App\Http\Controllers\Payout;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AdminPayoutController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Payout::query()
    //         ->with([
    //             'seller',
    //             'bankAccount',
    //         ])
    //         ->withCount('items')
    //         ->orderByDesc('requested_at');

    //     // if ($request->filled('status')) {
    //     //     $query->where('status', $request->status);
    //     // }

    //     // if ($request->filled('q')) {
    //     //     $search = $request->q;

    //     //     $query->where(function ($query) use ($search) {
    //     //         $query
    //     //             ->where('id', $search)
    //     //             ->orWhereHas('seller', function ($sellerQuery) use ($search) {
    //     //                 $sellerQuery
    //     //                     ->where('name', 'like', "%{$search}%")
    //     //                     ->orWhere('email', 'like', "%{$search}%");
    //     //             })
    //     //             ->orWhereHas('bankAccount', function ($bankQuery) use ($search) {
    //     //                 $bankQuery
    //     //                     ->where('bank_name', 'like', "%{$search}%")
    //     //                     ->orWhere('account_number', 'like', "%{$search}%");
    //     //             });
    //     //     });
    //     // }

    //     $payouts = $query
    //         ->paginate(15)
    //         ->withQueryString();

    //     return view(
    //         'admin.payout.index',
    //         compact('payouts')
    //     );
    // }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Payout::STATUSES)],
        ]);

        $payouts = Payout::query()
            ->with(['seller', 'bankAccount'])
            ->withCount('items')
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->orderByDesc('requested_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.payout.index', compact('payouts'));
    }

    public function show(Payout $payout)
    {
        $payout->load([
            'seller',
            'bankAccount',
            'processor',

            'items.transaction',
            'items.content',
            'items.folder',
            'items.license',
        ]);

        $calculatedAmount = round(
            (float) $payout->items->sum('seller_amount'),
            2
        );

        $storedAmountCents = (int) round(
            ((float) $payout->amount) * 100
        );

        $calculatedAmountCents = (int) round(
            $calculatedAmount * 100
        );

        $amountMatches = $storedAmountCents === $calculatedAmountCents;

        return view(
            'admin.payout.show',
            compact(
                'payout',
                'calculatedAmount',
                'amountMatches'
            )
        );
    }

    public function markPaid(Request $request, Payout $payout)
    {
        $validated = $request->validate([
            'admin_note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use (
            $payout,
            $validated
        ) {
            $lockedPayout = Payout::query()
                ->whereKey($payout->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayout->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payout' =>
                    'Only pending payouts can be marked as paid.',
                ]);
            }

            $items = TransactionItem::query()
                ->where(
                    'payout_id',
                    $lockedPayout->id
                )
                ->with('transaction')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'This payout does not contain any transaction items.',
                ]);
            }

            $containsOtherSeller = $items->contains(
                fn($item) =>
                (int) $item->seller_id
                    !== (int) $lockedPayout->seller_id
            );

            if ($containsOtherSeller) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'Payout contains transaction items belonging to another seller.',
                ]);
            }

            $containsInvalidTransaction = $items->contains(
                function ($item) {
                    return ! $item->transaction
                        || $item->transaction->status !== 'completed'
                        || $item->transaction->payment_status !== 'success';
                }
            );

            if ($containsInvalidTransaction) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'One or more payout items are not from successful transactions.',
                ]);
            }

            $calculatedAmount = round(
                (float) $items->sum('seller_amount'),
                2
            );

            $storedAmount = round(
                (float) $lockedPayout->amount,
                2
            );

            if ($calculatedAmount !== $storedAmount) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'Payout amount does not match the related transaction items.',
                ]);
            }

            $lockedPayout->load('bankAccount');

            if (
                ! $lockedPayout->bankAccount
                ||
                (int) $lockedPayout->bankAccount->seller_id
                !== (int) $lockedPayout->seller_id
            ) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'The payout bank account does not belong to this seller.',
                ]);
            }

            $lockedPayout->update([
                'status' => 'paid',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'admin_note' => $validated['admin_note'] ?? null,
            ]);
        });

        return redirect()
            ->route(
                'admin.payout.show',
                $payout
            )
            ->with(
                'success',
                'Payout has been marked as paid.'
            );
    }

    public function reject(
        Request $request,
        Payout $payout
    ) {
        $validated = $request->validate([
            'admin_note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        DB::transaction(function () use (
            $payout,
            $validated
        ) {
            $lockedPayout = Payout::query()
                ->whereKey($payout->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayout->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payout' =>
                    'Only pending payouts can be rejected.',
                ]);
            }

            $items = TransactionItem::query()
                ->where(
                    'payout_id',
                    $lockedPayout->id
                )
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'This payout does not contain any transaction items.',
                ]);
            }

            $containsOtherSeller = $items->contains(
                fn($item) =>
                (int) $item->seller_id
                    !== (int) $lockedPayout->seller_id
            );

            if ($containsOtherSeller) {
                throw ValidationException::withMessages([
                    'payout' =>
                    'Payout contains transaction items belonging to another seller.',
                ]);
            }

            $lockedPayout->update([
                'status' => 'rejected',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'admin_note' => $validated['admin_note'],
            ]);

            TransactionItem::query()
                ->where(
                    'payout_id',
                    $lockedPayout->id
                )
                ->update([
                    'payout_id' => null,
                ]);
        });

        return redirect()
            ->route(
                'admin.payout.show',
                $payout
            )
            ->with(
                'success',
                'Payout has been rejected and the earnings are available to the seller again.'
            );
    }
}
