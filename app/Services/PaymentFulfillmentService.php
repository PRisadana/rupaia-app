<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PaymentFulfillmentService
{
    public function __construct(
        private BundleZipService $bundleZipService,
        private EditingService $editingService
    ) {}


    public function complete(Transaction $transaction): Transaction
    {
        return DB::transaction(
            function () use ($transaction) {
                $transaction = Transaction::query()
                    ->whereKey($transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $transaction->load([
                    'items.content',
                    'items.folder',
                    'items.preset',
                ]);

                $commissionRate = (float) config('marketplace.platform_commission_rate');

                if ($commissionRate < 0 || $commissionRate > 1) {
                    throw new RuntimeException(
                        'Invalid platform commission rate.'
                    );
                }

                $paidAt = $transaction->paid_at ?? now();

                $transactionUpdates = [];

                if ($transaction->status !== 'completed') {
                    $transactionUpdates['status'] =
                        'completed';
                }

                if ($transaction->payment_status !== 'success') {
                    $transactionUpdates['payment_status'] =
                        'success';
                }

                if (! $transaction->paid_at) {
                    $transactionUpdates['paid_at'] =
                        $paidAt;
                }

                if (! $transaction->invoice_number) {
                    $transactionUpdates['invoice_number'] =
                        $this->generateInvoiceNumber(
                            $transaction,
                            $paidAt
                        );
                }

                if (! empty($transactionUpdates)) {
                    $transaction->update(
                        $transactionUpdates
                    );
                }

                foreach ($transaction->items as $item) {
                    $itemUpdates = [];

                    if ($item->commission_amount === null || $item->seller_amount === null) {
                        $price = round((float)$item->price_snapshot, 2);
                        $commission = round($price * $commissionRate, 2);
                        $sellerAmount = round($price - $commission, 2);

                        if ($item->commission_amount === null) {
                            $itemUpdates['commission_amount'] = $commission;
                        }

                        if ($item->seller_amount === null) {
                            $itemUpdates['seller_amount'] = $sellerAmount;
                        }
                    }

                    if (! $item->download_available_at) {
                        $itemUpdates['download_available_at'] = $paidAt;
                    }

                    if (! $item->download_expires_at) {
                        $itemUpdates['download_expires_at'] = $paidAt
                            ->copy()
                            ->addDays(30);
                    }

                    if (
                        in_array(
                            $item->item_type,
                            [
                                'content',
                                'content_with_preset',
                            ],
                            true
                        )
                        &&
                        $item->content
                    ) {
                        if ($item->item_type === 'content' && ! $item->final_file_path) {
                            $itemUpdates['final_file_path'] =
                                $this->copyOriginalForBuyer($item);
                        }

                        if (
                            $item->item_type
                            === 'content_with_preset'
                            &&
                            $item->content
                        ) {
                            if (
                                ! $item->preset_id
                                ||
                                ! $item->preset
                            ) {
                                throw new RuntimeException(
                                    'Preset transaction item does not have a valid preset.'
                                );
                            }

                            if (! $item->final_file_path) {
                                $itemUpdates['final_file_path'] =
                                    $this->editingService
                                    ->generateFinal(
                                        $item->content,
                                        $item->preset,
                                        $item
                                    );
                            }
                        }

                        if ($item->content->sale_type === 'single_sale') {
                            $content =
                                Content::query()
                                ->whereKey(
                                    $item->content_id
                                )
                                ->lockForUpdate()
                                ->first();

                            if (
                                $content && $content->sale_status !== 'sold_out'
                            ) {
                                $content->update([
                                    'sale_status'
                                    => 'sold_out',
                                ]);
                            }
                        }
                    }

                    if ($item->item_type === 'bundle' && $item->folder && ! $item->final_file_path) {
                        $itemUpdates['final_file_path'] =
                            $this->bundleZipService->generate(
                                $transaction,
                                $item
                            );
                    }

                    if (! empty($itemUpdates)) {
                        $item->update(
                            $itemUpdates
                        );
                    }
                }

                Log::info(
                    'Payment fulfillment completed.',
                    [
                        'transaction_id'
                        => $transaction->id,

                        'order_code'
                        => $transaction->order_code,
                    ]
                );

                return $transaction->fresh([
                    'items',
                ]);
            }
        );
    }


    private function generateInvoiceNumber(Transaction $transaction, $paidAt): string
    {
        return sprintf(
            'INV-%s-%08d',
            $paidAt->format('Ymd'),
            $transaction->id
        );
    }

    private function copyOriginalForBuyer(TransactionItem $item): string
    {
        $sourcePath = $item->content->path_hi_res;

        if (! Storage::disk('local')->exists($sourcePath)) {
            throw new RuntimeException('Hi-res source image not found.');
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';

        $finalPath = sprintf(
            'transactions/%d/items/%d/final-original.%s',
            $item->transaction_id,
            $item->id,
            $extension
        );

        Storage::disk('local')->copy($sourcePath, $finalPath);

        return $finalPath;
    }
}
