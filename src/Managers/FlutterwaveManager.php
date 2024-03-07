<?php

namespace Lunar\Flutterwave\Managers;

use Flutterwave\Flutterwave;
use Illuminate\Support\Collection;
use Lunar\Models\Cart;
use Lunar\Models\Transaction;

class FlutterwaveManager
{
    public function __construct()
    {
        Flutterwave::setUp([
            'secret_key' => config('services.flutterwave.secret_key'),
            'public_key' => config('services.flutterwave.public_key'),
            'encryption_key' => config('services.flutterwave.encryption_key'),
            'environment' => config('app.env'),
        ]);
    }

    /**
     * Return the Flutterwave client
     */
    public function getClient(): Flutterwave
    {
        return new Flutterwave();
    }

    /**
     * Create a payment intent from a Cart
     *
     * @return Transaction
     */
    public function createTransaction(Cart $cart)
    {
        $shipping = $cart->shippingAddress;

        $meta = (array) $cart->meta;

        if ($meta && ! empty($meta['transaction_id'])) {
            $transaction = new Transaction();
            //            $intent = $this->fetchTransaction(
            //                $meta['transaction_id']
            //            );

            if ($transaction) {
                return $transaction;
            }
        }

        $paymentIntent = $this->buildTransaction(
            $cart->total->value,
            $cart->currency->code,
            $shipping,
        );

        if (! $meta) {
            $cart->update([
                'meta' => [
                    'transaction_id' => $paymentIntent->id,
                ],
            ]);
        } else {
            $meta['payment_intent'] = $paymentIntent->id;
            $cart->meta = $meta;
            $cart->save();
        }

        return $paymentIntent;
    }

    public function syncTransaction(Cart $cart)
    {
        $meta = (array) $cart->meta;

        if (empty($meta['payment_intent'])) {
            return;
        }

        $cart = $cart->calculate();

        //        $this->getClient()->paymentIntents->update(
        //            $meta['payment_intent'],
        //            ['amount' => $cart->total->value]
        //        );
    }

    /**
     * Fetch an intent from the Flutterwave API.
     *
     * @param  string  $intentId
     * @return null|Transaction
     */
    public function fetchTransaction($transactionId)
    {
        return Transaction::find($transactionId);
    }

    public function getCharges(string $paymentIntentId): Collection
    {
        try {
            return collect(
                //                $this->getClient()->charges->all([
                //                    'payment_intent' => $paymentIntentId,
                //                ])['data'] ?? null
            );
        } catch (\Exception $e) {
            //
        }

        return collect();
    }

    /**
     * Build the intent
     *
     * @param  int  $value
     * @param  string  $currencyCode
     * @param  \Lunar\Models\CartAddress  $shipping
     */
    protected function buildTransaction($value, $currencyCode, $shipping)
    {
        return new \stdClass();
        //        return PaymentIntent::create([
        //            'amount' => $value,
        //            'currency' => $currencyCode,
        //            'automatic_payment_methods' => ['enabled' => true],
        //            'capture_method' => config('lunar.flutterwave.policy', 'automatic'),
        //            'shipping' => [
        //                'name' => "{$shipping->first_name} {$shipping->last_name}",
        //                'address' => [
        //                    'city' => $shipping->city,
        //                    'country' => $shipping->country->iso2,
        //                    'line1' => $shipping->line_one,
        //                    'line2' => $shipping->line_two,
        //                    'postal_code' => $shipping->postcode,
        //                    'state' => $shipping->state,
        //                ],
        //            ],
        //        ]);
    }
}
