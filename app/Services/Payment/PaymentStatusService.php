<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class PaymentStatusService
{
    public function __construct(private PaymentFulfillmentService $fulfillment) {}

    public function apply(Transaction $transaction, string $transactionStatus, ?string $fraudStatus = null): void
    {
        $isSuccess = $transactionStatus === 'settlement' ||
            (
                $transactionStatus === 'capture'
                &&
                $fraudStatus === 'accept'
            );

        if ($isSuccess) {
            $this->fulfillment->markAsPaid($transaction);
            return;
        }

        if ($transaction->payment_status === 'success') {
            return;
        }

        if ($transactionStatus === 'pending') {
            $transaction->update([
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            return;
        }

        if ($transactionStatus === 'expire') {
            $transaction->update([
                'status' => 'cancelled',
                'payment_status' => 'expired',
            ]);

            return;
        }

        if (in_array(
            $transactionStatus,
            [
                'deny',
                'cancel',
                'failure',
            ],
            true
        )) {
            $transaction->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);

            return;
        }

        Log::warning(
            'Unhandled Midtrans transaction status.',
            [
                'order_code' => $transaction->order_code,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
            ]
        );
    }
}
