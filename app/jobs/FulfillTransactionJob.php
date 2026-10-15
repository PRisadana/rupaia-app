<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\PaymentFulfillmentService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class FulfillTransactionJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public int $uniqueFor = 900;

    public function __construct(public int $transactionId) {}

    public function backoff(): array
    {
        return [30, 120];
    }

    public function uniqueId(): string
    {
        return (string) $this->transactionId;
    }

    public function handle(PaymentFulfillmentService $paymentFulfillmentService): void
    {
        $transaction = Transaction::find($this->transactionId);

        if (! $transaction) {
            return;
        }

        $paymentFulfillmentService->fulfill($transaction);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Transaction fulfillment failed permanently.', [
            'transaction_id' => $this->transactionId,
            'error' => $exception->getMessage(),
        ]);
    }
}
