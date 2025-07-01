<?php

namespace Webkul\Payment\Providers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Payment\Payment;

class PayTR extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'paytr';

    /**
     * PayTR API URL
     */
    const PAYTR_API_URL = 'https://www.paytr.com/odeme/api/get-token';
    const PAYTR_TEST_API_URL = 'https://www.paytr.com/odeme/api/get-token';

    /**
     * Get payment method configuration
     */
    public function getConfigData($field)
    {
        return core()->getConfigData('payment.paytr.' . $field);
    }

    /**
     * Is payment method active
     */
    public function isAvailable()
    {
        return $this->getConfigData('active');
    }

    /**
     * Get merchant credentials
     */
    public function getMerchantCredentials()
    {
        return [
            'merchant_id' => $this->getConfigData('merchant_id'),
            'merchant_key' => $this->getConfigData('merchant_key'),
            'merchant_salt' => $this->getConfigData('merchant_salt'),
            'test_mode' => $this->getConfigData('sandbox'),
        ];
    }

    /**
     * Create payment token for PayTR
     */
    public function createPaymentToken($order)
    {
        $credentials = $this->getMerchantCredentials();

        // Sipariş bilgileri
        $merchant_id = $credentials['merchant_id'];
        $merchant_key = $credentials['merchant_key'];
        $merchant_salt = $credentials['merchant_salt'];

        $email = $order->customer_email;
        $payment_amount = number_format($order->grand_total * 100, 0, '.', ''); // PayTR kuruş cinsinden
        $merchant_oid = $order->id;
        $user_name = $order->customer_first_name . ' ' . $order->customer_last_name;
        $user_address = $order->billing_address->address1;
        $user_phone = $order->billing_address->phone;

        // Sepet içeriği
        $user_basket = $this->getUserBasket($order);

        // Callback URLs
        $merchant_ok_url = route('paytr.success');
        $merchant_fail_url = route('paytr.cancel');

        // Test modu kontrolü
        $test_mode = $credentials['test_mode'] ? '1' : '0';

        // Hash oluşturma
        $hash_str = $merchant_id . $user_basket . $merchant_oid . $email . $payment_amount . $user_name . $user_address . $user_phone . $merchant_salt;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str, $merchant_key, true));

        // PayTR API'ye gönderilecek data
        $post_data = [
            'merchant_id' => $merchant_id,
            'user_ip' => request()->ip(),
            'merchant_oid' => $merchant_oid,
            'email' => $email,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => 1,
            'no_installment' => 0,
            'max_installment' => 0,
            'user_name' => $user_name,
            'user_address' => $user_address,
            'user_phone' => $user_phone,
            'merchant_ok_url' => $merchant_ok_url,
            'merchant_fail_url' => $merchant_fail_url,
            'timeout_limit' => 30,
            'currency' => 'TL',
            'test_mode' => $test_mode
        ];

        return $this->sendPayTRRequest($post_data);
    }

    /**
     * Send request to PayTR API
     */
    private function sendPayTRRequest($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::PAYTR_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);

        if (curl_error($ch)) {
            curl_close($ch);
            throw new \Exception('PayTR API Connection Error: ' . curl_error($ch));
        }

        curl_close($ch);

        $result = json_decode($result, true);

        if ($result['status'] == 'success') {
            return $result['token'];
        } else {
            throw new \Exception('PayTR Token Error: ' . $result['reason']);
        }
    }

    /**
     * Get user basket for PayTR
     */
    private function getUserBasket($order)
    {
        $user_basket = [];

        foreach ($order->items as $item) {
            $user_basket[] = [
                $item->name,
                number_format($item->price * 100, 0, '.', ''), // Kuruş cinsinden
                $item->qty_ordered
            ];
        }

        // Kargo ücreti varsa ekle
        if ($order->shipping_amount > 0) {
            $user_basket[] = [
                'Kargo',
                number_format($order->shipping_amount * 100, 0, '.', ''),
                1
            ];
        }

        return base64_encode(json_encode($user_basket));
    }

    /**
     * Verify PayTR callback
     */
    public function verifyCallback($request)
    {
        $credentials = $this->getMerchantCredentials();

        $post = $request->all();
        $hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $credentials['merchant_salt'] . $post['status'] . $post['total_amount'], $credentials['merchant_key'], true));

        if ($hash != $post['hash']) {
            throw new \Exception('PayTR Hash verification failed');
        }

        return [
            'status' => $post['status'] == 'success',
            'order_id' => $post['merchant_oid'],
            'amount' => $post['total_amount'] / 100, // TL cinsine çevir
            'transaction_id' => $post['payment_id'] ?? null
        ];
    }

    /**
     * Get redirect URL for payment
     */
    public function getRedirectUrl()
    {
        return route('paytr.redirect');
    }

    /**
     * Get payment method additional information
     */
    public function getAdditionalDetails()
    {
        return [
            'title' => core()->getConfigData('payment.paytr.title') ?: 'PayTR',
            'description' => core()->getConfigData('payment.paytr.description') ?: 'Güvenli ödeme için PayTR kullanın',
            'sort' => core()->getConfigData('payment.paytr.sort') ?: 1,
        ];
    }
}