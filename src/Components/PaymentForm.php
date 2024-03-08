<?php

namespace Lunar\Flutterwave\Components;

use Livewire\Component;
use Lunar\Flutterwave\Facades\FlutterwaveFacade;
use Lunar\Models\Cart;

class PaymentForm extends Component
{
    /**
     * The instance of the order.
     *
     * @var Order
     */
    public Cart $cart;

    /**
     * The config values.
     */
    public array $config;

    /**
     * The return URL on a successful transaction
     *
     * @var string
     */
    public $returnUrl;

    /**
     * The payment type selected
     *
     * @var string
     */
    public $paymentType;

    /**
     * The policy for handling payments.
     *
     * @var string
     */
    public $policy;

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cardDetailsSubmitted',
    ];

    /**
     * {@inheritDoc}
     */
    public function mount()
    {
        $this->setConfig();
        $this->policy = config('lunar.flutterwave.policy', 'capture');
    }

    /**
     * Return the client secret for Payment Intent
     *
     * @return void
     */
    public function getClientSecretProperty()
    {
        $intent = FlutterwaveFacade::createIntent($this->cart);

        return $intent->client_secret;
    }

    /**
     * Return the carts billing address.
     *
     * @return void
     */
    public function getBillingProperty()
    {
        return $this->cart->billingAddress;
    }

    private function setConfig(): void
    {
        $this->config = [
            'payment_modal_title' => config('lunar.flutterwave.payment_modal_title', config('app.name', 'Lunar Store')),
            'payment_modal_description' => config('lunar.flutterwave.payment_modal_description', 'Checkout at Lunar Store'),
            'app_logo' => config('lunar.flutterwave.app_logo', 'https://checkout.flutterwave.com/assets/img/rave-logo.png'),
            'public_key' => config('services.flutterwave.public_key'),
            'payment_options' => config('lunar.flutterwave.payment_options'),
            'tx_ref' => app(config(
                'flutterwave.actions.generate_transaction_reference',
                \Lunar\Flutterwave\Actions\GenerateTransactionReference::class)
            )->execute(),
        ];
    }

    public function getMetaDataProperty(): string
    {
        return json_encode([
            'source' => 'docs-inline-test',
            'consumer_mac' => '92a3-912ba-1192a',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('lunar::flutterwave.components.payment-form');
    }
}
