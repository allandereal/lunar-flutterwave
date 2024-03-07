<?php

namespace Lunar\Flutterwave\Actions;

use Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

class StoreTransaction
{
    public function store(Order $order, ?FlutterwaveTransaction $flTransaction)
    {
        /**
         * If charges are empty, there is nothing to update.
         */
        if (is_null($flTransaction)) {
            return $order;
        }

        /**
         * Get the most up-to-date transactions.
         */
        $transactions = $order->transactions()->get();

        $timestamp = now()->createFromTimestamp($flTransaction->created_at);

        $transaction = $transactions->first(
            fn ($t) => $t->reference == $flTransaction->id
        ) ?: new Transaction;

        $type = 'capture';

        if (! $flTransaction->captured) {
            $type = 'intent';
        }

        //        if ($flTransaction->amount_refunded && $flTransaction->amount_refunded < $flTransaction->amount) {
        //            $type = 'refund';
        //        }

        $paymentType = $flTransaction->payment_type;

        /**
         * "card": {
         * "first_6digits": "539923",
         * "last_4digits": "2526",
         * "issuer": "MASTERCARD FIRST BANK OF NIGERIA PLC DEBIT CARD",
         * "country": "NG",
         * "type": "MASTERCARD",
         * "expiry": "01/23"
         * }
         */
        $paymentDetails = $flTransaction->card;

        $lastFour = null;
        $cardType = $paymentType;
        $meta = [];

        if (! empty($paymentDetails['type'])) {
            $cardType = $paymentDetails['type'];
        }

        if (! empty($paymentDetails['last_4digits'])) {
            $lastFour = $paymentDetails['last_4digits'];
        }

        if (! empty($flTransaction['id'])) {
            $meta = array_merge($meta, (array) ($flTransaction));
        }

        $transaction->fill([
            'order_id' => $order->id,
            'success' => $flTransaction->status == FlutterwaveTransaction::STATUS_SUCCESSFUL,
            'type' => $type,
            'driver' => 'flutterwave',
            'amount' => $flTransaction->amount,
            'reference' => $flTransaction->id,
            'status' => $flTransaction->status,
            'notes' => $flTransaction->narraation,
            'card_type' => $cardType,
            'last_four' => $lastFour,
            'captured_at' => $flTransaction->amount_captured ? $timestamp : null,
            'meta' => $meta,
        ]);

        $transaction->save();

        return $order;
    }
}
