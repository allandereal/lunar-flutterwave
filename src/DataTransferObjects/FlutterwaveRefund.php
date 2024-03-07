<?php

namespace Lunar\Flutterwave\DataTransferObjects;

use Flutterwave\Contract\ConfigInterface;
use stdClass;

class FlutterwaveRefund extends stdClass
{
    const STATUS_COMPLETED = 'completed';

    /**
     *
     * @property string $id
     * @property string $account_id
     * @property int|string $tx_id
     * @property string $flw_ref
     * @property int|string $wallet_id
     * @property float|int $amount_refunded
     * @property string $status
     * @property string $destination
     * @property stdClass $meta
     * @property string $created_at
     */
    public function __construct(stdClass $dataObject)
    {
        foreach ($dataObject as $property => $value) {
            if (property_exists(self::class, $property)) {
                $this->$property = $value;
            }
        }
    }
}
