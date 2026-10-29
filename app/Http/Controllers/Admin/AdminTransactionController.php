<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\MidtransPaymentService;
use App\Services\Payment\PaymentStatusService;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\validation\Rule;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Transaction::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Transaction::PAYMENT_STATUSES)],
        ]);

        $transactions = Transaction::query()
            ->with('buyer')
            ->withCount('items')
            ->filterAdmin($filters)
            ->latest()
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
