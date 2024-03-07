<?php

namespace Lunar\Flutterwave\Actions;

use Lunar\Flutterwave\Concerns\ConstructsWebhookEvent;

class ConstructWebhookEvent implements ConstructsWebhookEvent
{
    public function constructEvent(string $jsonPayload, string $signature, string $secret)
    {
        self::verifyHeader($jsonPayload, $signature, $secret);

        $data = \json_decode($jsonPayload, true);
        $jsonError = \json_last_error();
        if ($data === null && $jsonError !== \JSON_ERROR_NONE) {
            $msg = "Invalid payload: {$jsonPayload} "
                ."(json_last_error() was {$jsonPayload})";

            throw new \UnexpectedValueException($msg);
        }

        return $data;
    }

    private function verifyHeader($jsonPayload, $signature, $secret)
    {
        return config('services.flutterwave.encryption_key') == $secret;
    }
}
