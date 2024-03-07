<?php

namespace Lunar\Flutterwave\Actions;

use Illuminate\Support\Str;

class GenerateTransactionReference
{
    public function execute(): string
    {
        return 'txref-'.Str::random(9).time();
    }
}
