<?php

namespace Lunar\Flutterwave;

use Exception;
use Flutterwave\Flutterwave;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Flutterwave\Actions\UpdateOrderFromTransaction;
use Lunar\Flutterwave\DataTransferObjects\FlutterwaveRefund;
use Lunar\Flutterwave\DataTransferObjects\FlutterwaveTransaction;
use Lunar\Flutterwave\Facades\FlutterwaveFacade;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;

class FlutterwavePaymentType extends AbstractPayment
{
    /**
     * The Flutterwave instance.
     */
    protected Flutterwave $flutterwave;

    /**
     * The flutterwave transaction object.
     */
    protected ?\stdClass $transaction;

    protected ?\stdClass $refund;

    /**
     * The policy when capturing payments.
     *
     * @var string
     */
    protected $policy;

    /**
     * Initialise the payment type.
     */
    public function __construct()
    {
        $this->flutterwave = FlutterwaveFacade::getClient();

        $this->policy = config('lunar.flutterwave.policy', 'automatic');
    }

    /**
     * Authorize the payment for processing.
     */
    final public function authorize(): PaymentAuthorize
    {
        $this->orders = new \Illuminate\Database\Eloquent\Collection($this->cart->draftOrder);
        if ($this->orders->isEmpty()){
            $this->orders = $this->cart->completedOrders;
        }

        if ($this->orders->isEmpty()) {
            try {
                $this->orders = $this->cart->createOrders();
            } catch (DisallowMultipleCartOrdersException $e) {
                return new PaymentAuthorize(
                    success: false,
                    message: $e->getMessage(),
                );
            }
        }

        $transactionId = $this->data['transaction_id'];
        try {
            $response = (new FlutterwaveTransactionClient($this->flutterwave->getConfig()))->verify($transactionId);
        } catch (Exception $e) {
            return new PaymentAuthorize(
                success: false,
                message: $e->getMessage(),
                orderId: $this->orders->pluck('id')->toArray(),
            );
        }

        $this->transaction = $response->data ?? null;

        if ($this->transaction->status != FlutterwaveTransaction::STATUS_SUCCESSFUL) {
            return new PaymentAuthorize(
                success: false,
                message: "Transaction {$this->transaction->status}",
                orderId: $this->orders->pluck('id')->toArray(),
            );
        }

        if ($this->cart) {
            if (! ($this->cart->meta['transaction_id'] ?? null)) {
                $this->cart->update([
                    'meta' => [
                        'transaction_id' => $this->transaction->id,
                        'tx_ref' => $this->transaction->tx_ref,
                    ],
                ]);
            } else {
                $this->cart->meta['transaction_id'] = $this->transaction->id;
                $this->cart->meta['tx_ref'] = $this->transaction->tx_ref;
                $this->cart->save();
            }
        }

        $orders = UpdateOrderFromTransaction::execute($this->orders, $this->transaction);

        return new PaymentAuthorize(
            success: (bool) $orders->first()->placed_at, //TODO: confirm if checking only one order is enough
            message: $this->transaction->processor_response,
            orderId: $this->orders->pluck('id')->toArray()
        );
    }

    /**
     * Capture a payment for a transaction.
     *
     * @param  int  $amount
     */
    public function capture(Transaction $transaction, $amount = 0): PaymentCapture
    {
        $payload = [];

        if ($amount > 0) {
            $payload['amount'] = $amount;
        }

        $transaction = FlutterwaveFacade::fetchTransaction($transaction->id);

        if ($transaction?->status != FlutterwaveTransaction::STATUS_SUCCESSFUL) {
            try {
                //TODO: retry payment request on flutterwave transaction
            } catch (Exception $e) {
                return new PaymentCapture(
                    success: false,
                    message: $e->getMessage()
                );
            }

            UpdateOrderFromTransaction::execute($transaction->order, $transaction);
        }

        return new PaymentCapture(success: true);
    }

    /**
     * Refund a captured transaction
     *
     * @param  string|null  $notes
     */
    public function refund(Transaction $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $refund = (new Transactions($this->flutterwave->getConfig()))->refund($transaction->reference);
        } catch (Exception $e) {
            return new PaymentRefund(
                success: false,
                message: $e->getMessage()
            );
        }

        $this->refund = (new FlutterwaveRefund($refund->data ?? null));

        $transaction->order->transactions()->create([
            'success' => $refund->status == FlutterwaveRefund::STATUS_COMPLETED,
            'type' => 'refund',
            'driver' => 'flutterwave',
            'amount' => $refund->amount_refunded,
            'reference' => $refund->id,
            'status' => $refund->status,
            'notes' => $notes,
            'card_type' => $transaction->card_type,
            'last_four' => $transaction->last_four,
        ]);

        return new PaymentRefund(
            success: true
        );
    }
}
