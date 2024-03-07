<?php

namespace Lunar\Flutterwave\Pipelines;

use Lunar\Flutterwave\DataTransferObjects\OrderIntent;

class UpdateOrderFromTransaction
{
    public function handle(OrderIntent $orderIntent, \Closure $next)
    {

    }
}
