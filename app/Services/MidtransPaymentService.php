<?php

namespace App\Services;

use App\Models\Transaction;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class MidtransPaymentService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Transaction $transaction): string
    {
        $transaction->loadMissing('buyer');

        $grossAmount = (int) round((float) $transaction->total_amount);

        if ($grossAmount <= 0) {
            throw new \RuntimeException('Transaction amount must be greater than zero.');
        }

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->order_code,
                'gross_amount' => $grossAmount,
            ],

            'customer_details' => [
                'first_name' => $transaction->buyer->name,
                'email' => $transaction->buyer->email,
            ],
        ];

        return Snap::getSnapToken($params);
    }

    public function getTransactionStatus(Transaction $transaction): object
    {
        $response = MidtransTransaction::status($transaction->order_code);

        if (! is_object($response)) {
            throw new \RuntimeException(
                'Unexpected response format from Midtrans.'
            );
        }

        return $response;
    }
}
