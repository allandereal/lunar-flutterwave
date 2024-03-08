<?php

namespace Lunar\Flutterwave\DataTransferObjects;

use Lunar\Models\Order;

class OrderIntent
{
    public function __construct(
        public Order $order,
        public \stdClass $transaction
    ) {
    }
}
