<?php

namespace Lunar\Flutterwave\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction;
use Lunar\Models\Order;

class UpdateOrderFromTransaction
{
    final public static function execute(
        ?Collection $orders,
        ?\stdClass $transaction,
        string $successStatus = 'successful',
        string $failStatus = 'failed'
    ): Collection {
        return DB::transaction(function () use ($orders, $transaction) {
            $orders = app(StoreTransaction::class)->store($orders, $transaction);

            $statuses = config('lunar.flutterwave.status_mapping', []);

            $placedAt = null;

            if ($transaction->status == FlutterwaveTransaction::STATUS_SUCCESSFUL) {
                $placedAt = now();
            }

            return $orders->map(function ($order) use($statuses, $transaction, $placedAt){
                return $order->update([
                    'status' => $statuses[$transaction->status] ?? $transaction->status,
                    'placed_at' => $order->placed_at ?: $placedAt,
                ]);
            });
        });
    }
}
