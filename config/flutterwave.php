<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Webhook path
    |--------------------------------------------------------------------------
    |
    | Set what the path should be for the webhook you set up in Flutterwave.
    |
    */
    'webhook_path' => 'flutterwave/webhook',

    /*
    |--------------------------------------------------------------------------
    | Capture policy
    |--------------------------------------------------------------------------
    |
    | Here is where you can set whether you want to capture and charge payments
    | straight away, or create the Payment Intent and release them at a later date.
    |
    | automatic - Capture the payment straight away.
    | manual - Don't take payment straight away and capture later.
    |
    */
    'policy' => 'automatic',

    /*
    |--------------------------------------------------------------------------
    | Status mapping
    |--------------------------------------------------------------------------
    |
    | When a payment intent is retrieved from Flutterwave it will have a status which is
    | unique to Flutterwave and potentially not what you have in Lunar. Here you can define
    | what each Flutterwave status should be in Lunar.
    |
    | Reference: https://flutterwave.com/docs/api/charges/object
    */
    'status_mapping' => [
        \Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction::STATUS_SUCCESSFUL => 'payment-received',
        \Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction::STATUS_CANCELED => 'canceled',
        \Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction::STATUS_PROCESSING => 'processing',
        \Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction::STATUS_PENDING => 'pending',
        \Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction::STATUS_FAILED => 'failed',
    ],

    'actions' => [
        /*
        |--------------------------------------------------------------------------
        | Store charges
        |--------------------------------------------------------------------------
        |
        | A payment intent might have a number of charges associated to them, these
        | could be either pending, captured or refunds. This action takes the charges
        | which are associated to the payment intent and stores them against the order.
        |
        | Reference: https://flutterwave.com/docs/api/charges/object
        */
        'store_charges' => \Lunar\Flutterwave\Actions\StoreTransaction::class,

        'generate_transaction_reference' => \Lunar\Flutterwave\Actions\GenerateTransactionReference::class,
    ],

    'payment_options' => 'mobilemoneyuganda, card, mpesa, banktransfer, ussd',
];
