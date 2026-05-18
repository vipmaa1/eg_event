<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private string $paymobApiKey;
    private string $paymobHmac;
    private string $fawryMerchant;
    private string $fawrySecurity;

    public function __construct()
    {
        $this->paymobApiKey = config('services.paymob.api_key');
        $this->paymobHmac = config('services.paymob.hmac_secret');
        $this->fawryMerchant = config('services.fawry.merchant_code');
        $this->fawrySecurity = config('services.fawry.security_code');
    }

    public function createPaymobPayment(array $data): array
    {
        try {
            $auth = Http::post('https://accept.paymob.com/api/auth/tokens', [
                'api_key' => $this->paymobApiKey,
            ])->throw()->json();

            $token = $auth['token'] ?? null;
            if (!$token) {
                throw new \RuntimeException('Paymob auth failed');
            }

            $order = Http::post('https://accept.paymob.com/api/ecommerce/orders', [
                'auth_token' => $token,
                'delivery_needed' => 'false',
                'amount_cents' => (int) round($data['amount'] * 100),
                'currency' => $data['currency'] ?? 'EGP',
                'items' => [],
            ])->throw()->json();

            $paymentKey = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', [
                'auth_token' => $token,
                'amount_cents' => (int) round($data['amount'] * 100),
                'currency' => $data['currency'] ?? 'EGP',
                'order_id' => $order['id'],
                'billing_data' => [
                    'first_name' => $data['first_name'] ?? 'EventHub',
                    'last_name' => $data['last_name'] ?? 'User',
                    'email' => $data['email'] ?? '',
                    'phone_number' => $data['phone'] ?? '',
                    'apartment' => 'N/A',
                    'floor' => 'N/A',
                    'street' => 'N/A',
                    'building' => 'N/A',
                    'city' => $data['city'] ?? 'Cairo',
                    'country' => 'EG',
                    'state' => $data['city'] ?? 'Cairo',
                ],
                'integration_id' => (int) config('services.paymob.integration_id'),
                'lock_order_when_paid' => 'true',
            ])->throw()->json();

            return [
                'success' => true,
                'payment_key' => $paymentKey['token'] ?? null,
                'order_id' => $order['id'] ?? null,
                'ifram_url' => "https://accept.paymob.com/api/acceptance/iframes/" . config('services.paymob.iframe_id') . "?payment_token=" . ($paymentKey['token'] ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::error('Paymob payment failed', ['error' => $e->getMessage(), 'data' => $data]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function verifyPaymobHmac(array $data): bool
    {
        $keys = ['amount_cents', 'created_at', 'currency', 'error_occured', 'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure', 'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment', 'is_voided', 'order', 'owner', 'pending', 'source_data_type', 'source_data_pan', 'source_data_sub_type', 'success'];
        $concatenated = '';
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $concatenated .= $data[$key];
            }
        }
        $computed = hash_hmac('sha512', $concatenated, $this->paymobHmac);
        return hash_equals($computed, $data['hmac'] ?? '');
    }

    public function createFawryPayment(array $data): array
    {
        try {
            $merchantRefNum = $data['reference'] ?? uniqid('FW-', true);
            $signature = hash('sha256', $this->fawryMerchant . $merchantRefNum . $data['amount'] . $this->fawrySecurity);

            $response = Http::post('https://www.atfawry.com/ECommercePlugin/FawryPay', [
                'merchantCode' => $this->fawryMerchant,
                'merchantRefNum' => $merchantRefNum,
                'customerMobile' => $data['phone'] ?? '',
                'customerEmail' => $data['email'] ?? '',
                'customerName' => $data['name'] ?? 'EventHub User',
                'amount' => $data['amount'],
                'currencyCode' => $data['currency'] ?? 'EGP',
                'language' => $data['lang'] ?? 'ar',
                'chargeItems' => [],
                'signature' => $signature,
            ])->throw()->json();

            return [
                'success' => ($response['statusCode'] ?? 500) === 200,
                'reference' => $merchantRefNum,
                'redirect_url' => $response['paymentUrl'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Fawry payment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function verifyFawrySignature(array $data): bool
    {
        $merchantRefNum = $data['merchantRefNum'] ?? '';
        $amount = $data['paymentAmount'] ?? 0;
        $computed = hash('sha256', $this->fawryMerchant . $merchantRefNum . $amount . $this->fawrySecurity);
        return hash_equals($computed, $data['signature'] ?? '');
    }
}
