<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\MidtransPaymentService;
use App\Services\PaymentStatusService;
use Illuminate\Http\Request;
use Throwable;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()
            ->with('buyer')
            ->withCount('items')
            ->latest();

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // if ($request->filled('q')) {
        //     $search = $request->q;
        //     $query->where(
        //         function ($query) use ($search) {
        //             $query
        //                 ->where(
        //                     'order_code',
        //                     'like',
        //                     "%{$search}%"
        //                 )
        //                 ->orWhereHas(
        //                     'buyer',
        //                     function ($buyerQuery)
        //                     use ($search) {
        //                         $buyerQuery
        //                             ->where(
        //                                 'name',
        //                                 'like',
        //                                 "%{$search}%"
        //                             )
        //                             ->orWhere(
        //                                 'email',
        //                                 'like',
        //                                 "%{$search}%"
        //                             );
        //                     }
        //                 );
        //         }
        //     );
        // }

        $transactions = $query
            ->paginate(15)
            ->withQueryString();

        return view('admin.transaction.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load([
            'buyer',
            'items.seller',
            'items.content',
            'items.folder',
            'items.license',
        ]);

        return view('admin.transaction.show', compact('transaction'));
    }

    public function syncStatus(Transaction $transaction, MidtransPaymentService $midtrans, PaymentStatusService $paymentStatusService)
    {
        try {
            $response = $midtrans->getTransactionStatus($transaction);
        } catch (Throwable $e) {
            report($e);
            return back()
                ->withErrors([
                    'sync'
                    => 'Unable to retrieve transaction status from Midtrans.',
                ]);
        }

        $midtransOrderId = (string)($response->order_id ?? '');

        if ($midtransOrderId !== $transaction->order_code) {
            return back()
                ->withErrors([
                    'sync'
                    => 'Midtrans order ID does not match the local transaction.',
                ]);
        }

        $localAmount = number_format((float)$transaction->total_amount, 2, '.', '');

        $midtransAmount = number_format((float)($response->gross_amount ?? 0), 2, '.', '');

        if ($localAmount !== $midtransAmount) {
            return back()
                ->withErrors([
                    'sync'
                    => 'Midtrans transaction amount does not match the local transaction.',
                ]);
        }

        $transactionStatus = (string)($response->transaction_status ?? '');

        $fraudStatus = isset($response->fraud_status) ? (string) $response->fraud_status : null;

        try {
            $paymentStatusService->apply(
                $transaction,
                $transactionStatus,
                $fraudStatus
            );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'sync'
                    => 'Payment status could not be applied: ' . $e->getMessage(),
                ]);
        }

        return redirect()
            ->route(
                'admin.transaction.show',
                $transaction
            )
            ->with(
                'success',
                "Transaction status synchronized with Midtrans: {$transactionStatus}."
            );
    }
}
