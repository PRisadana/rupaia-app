<?php

namespace App\Http\Controllers\Payment;

use App\Models\Transaction;
use App\Services\Payment\MidtransPaymentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function create(Request $request, Transaction $transaction, MidtransPaymentService $midtrans)
    {
        $user = $request->user();
        if ($transaction->buyer_id !== $user->id) {
            abort(403);
        }

        if ($transaction->status !== 'pending' || $transaction->payment_status !== 'pending') {
            return redirect()
                ->route(
                    'transactions.show',
                    $transaction
                )
                ->withErrors([
                    'payment'
                    => 'This transaction is no longer available for payment.',
                ]);
        }

        if (! $transaction->snap_token) {
            try {
                $snapToken = $midtrans->createSnapToken($transaction);
                $transaction->update([
                    'snap_token' => $snapToken,
                ]);
            } catch (\Throwable $e) {
                report($e);
                return redirect()
                    ->route(
                        'transactions.show',
                        $transaction
                    )
                    ->withErrors([
                        'payment'
                        => 'Unable to initialize payment. Please try again.',
                    ]);
            }
        }

        return redirect()
            ->route(
                'transactions.show',
                $transaction
            )
            ->with(
                'open_midtrans',
                true
            );
    }
}
