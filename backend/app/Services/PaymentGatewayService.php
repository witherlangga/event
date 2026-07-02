<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    public function createPayment(Order $order, string $paymentMethod, ?string $bank = null): array
    {
        $apiKey = env('PAYMENT_GATEWAY_API_KEY');
        $baseUrl = env('PAYMENT_GATEWAY_URL');
        $callbackUrl = env('APP_URL') . '/api/payment/webhook';

        if (!$apiKey || !$baseUrl) {
            throw new \RuntimeException('Payment gateway is not configured.');
        }

        $payload = [
            'external_id' => 'order-' . $order->id,
            'amount' => $order->total_price,
            'payment_method' => $paymentMethod,
            'callback_url' => $callbackUrl,
            'description' => 'Pembayaran order #' . $order->id,
            'customer' => [
                'email' => $order->user->email,
                'name' => $order->user->name,
            ],
        ];

        if ($paymentMethod === 'bank_transfer' && $bank) {
            $payload['bank'] = $bank;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ])->post($baseUrl . '/payments', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Gateway request failed: ' . $response->body());
        }

        return $response->json();
    }
}
