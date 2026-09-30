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
        $params = [
            'transaction_details' => [
                'order_id' => $transaction->order_code,
                'gross_amount' => (int) $transaction->total_amount,
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
