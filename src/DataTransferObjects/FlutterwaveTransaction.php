<?php

namespace Lunar\Flutterwave\DataTransferObjects;

use Flutterwave\Contract\ConfigInterface;
use stdClass;

class FlutterwaveTransaction
{
    const STATUS_SUCCESSFUL = 'successful';

    const STATUS_CANCELED = 'canceled';

    const STATUS_PROCESSING = 'processing';

    const STATUS_PENDING = 'pending';

    const STATUS_FAILED = 'failed';

    /**
     * @property string $id
     * @property string $tx_ref
     * @property string $flw_ref
     * @property string $device_fingerprint
     * @property float|int $amount
     * @property string $currency
     * @property float|int $charged_amount
     * @property string $app_fee
     * @property float|int $merchant_fee
     * @property string $processor_response
     * @property string $auth_model
     * @property string $ip
     * @property string $narration
     * @property string $status
     * @property string $payment_type
     * @property string $created_at
     * @property string $account_id
     * @property stdClass $customer
     * @property stdClass $card
     */
}
