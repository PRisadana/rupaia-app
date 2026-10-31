<?php

namespace App\Http\Controllers\Payment;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\PaymentFulfillmentService;
use App\Services\Payment\PaymentStatusService;
use App\Http\Controllers\Controller;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, PaymentFulfillmentService $paymentFulfillmentService, PaymentStatusService $paymentStatusService)
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $signatureKey) {
            return response()->json([
                'message' => 'Invalid payload.',
            ], 400);
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));

        if (! hash_equals($expectedSignature, $signatureKey)) {
            Log::warning(
                'Invalid Midtrans signature.',
                [
                    'order_id' => $orderId,
                ]
            );

            return response()->json([
                'message' => 'Invalid signature.',
            ], 403);
        }

        $transaction = Transaction::where(
            'order_code',
            $orderId
        )->first();
        if (! $transaction) {
            return response()->json([
                'message' => 'Transaction not found.',
            ], 404);
        }

        if ((int) round((float) $transaction->total_amount) !== (int) round((float) $grossAmount)) {
            Log::warning(
                'Midtrans amount mismatch.',
                [
                    'order_id' => $orderId,
                ]
            );

            return response()->json([
                'message' => 'Amount mismatch.',
            ], 422);
        }

        $transactionStatus = $payload['transaction_status'] ?? null;

        $fraudStatus = $payload['fraud_status'] ?? null;

        $paymentStatusService->apply(
            $transaction,
            $transactionStatus,
            $fraudStatus
        );

        // if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
        //     $paymentFulfillmentService->complete($transaction);
        //     Log::info(
        //         'Midtrans payment fulfilled.',
        //         [
        //             'order_id'
        //             => $orderId,
        //         ]
        //     );
        // } elseif ($transactionStatus === 'pending') {
        //     $transaction->update([
        //         'status' => 'pending',
        //         'payment_status' => 'pending',
        //     ]);
        // } elseif (
        //     $transactionStatus === 'expire'
        // ) {
        //     $transaction->update([
        //         'status' => 'cancelled',
        //         'payment_status' => 'expired',
        //     ]);
        // } elseif (
        //     in_array(
        //         $transactionStatus,
        //         [
        //             'deny',
        //             'cancel',
        //             'failure',
        //         ],
        //         true
        //     )
        // ) {
        //     $transaction->update([
        //         'status' => 'cancelled',
        //         'payment_status' => 'failed',
        //     ]);
        // }

        return response()->json([
            'message' => 'Notification processed.',
        ]);
    }
}
