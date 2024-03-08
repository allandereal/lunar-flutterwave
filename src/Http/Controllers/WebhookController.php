<?php

namespace Lunar\Flutterwave\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Facades\Payments;
use Lunar\Flutterwave\Concerns\ConstructsWebhookEvent;
use Lunar\Models\Cart;

final class WebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = config('services.flutterwave.encryption_key');
        $flutterwaveSig = $request->header('Flutterwave-Signature');

        try {
            $event = app(ConstructsWebhookEvent::class)->constructEvent(
                $request->getContent(),
                $flutterwaveSig,
                $secret
            );
        } catch (\Exception $e) {
            Log::error(
                $error = $e->getMessage()
            );

            return response(status: 400)->json([
                'webhook_successful' => false,
                'message' => $error,
            ]);
        }

        $transactionId = $event->data->object->id;

        $cart = Cart::where('meta->transaction_id', '=', $transactionId)->first();

        if (! $cart) {
            Log::error(
                $error = "Unable to find cart with intent {$transactionId}"
            );

            return response(status: 400)->json([
                'webhook_successful' => false,
                'message' => $error,
            ]);
        }

        //        $payment = Payments::driver('flutterwave')->cart($cart->calculate())->withData([
        //            'transaction_id' => $transactionId,
        //            'tx_ref' => $event->data?->tx_ref,
        //        ])->authorize();

        //        PaymentAttemptEvent::dispatch($payment);

        return response()->json([
            'webhook_successful' => true,
            'message' => 'Webook handled successfully',
        ]);
    }
}
