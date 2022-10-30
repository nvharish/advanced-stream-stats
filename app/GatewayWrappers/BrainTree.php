<?php

namespace App\GatewayWrappers;

use Braintree\Customer;
use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\Exception;
use Braintree\Transaction;

class BrainTree {

    private const ENVIRONMENT = 'sandbox';
    private const MERCHANT_ID = 'wc235x4vk6wyh8p3';
    private const PUBLIC_KEY = '3c5bbzyjz77yj4zv';
    private const SECRET_KEY = 'a582d056aa8fd3f3fb84f8d870c79d8e';

    public function __construct() {
        Configuration::environment(self::ENVIRONMENT);
        Configuration::merchantId(self::MERCHANT_ID);
        Configuration::publicKey(self::PUBLIC_KEY);
        Configuration::privateKey(self::SECRET_KEY);
    }

    public function doPayment($args = array()) {
        $result = array();
        try {
            $customer = Customer::create([
                        'email' => $args['email'],
                        'firstName' => $args['first_name'],
                        'lastName' => $args['last_name'],
                        'paymentMethodNonce' => $args['payment_method_nonce']
            ]);

            if ($customer->success) {
                $result = $this->proceedToPay($customer->customer->id, $args);
            } else {
                $result['success'] = false;
                $result['transaction_status'] = 'Something went wrong';
                $result['response_text'] = serialize($customer);
            }
        } catch (Exception $ex) {
            $result['success'] = false;
            $result['transaction_status'] = 'Something went wrong';
            $result['response_text'] = serialize($customer);
        }
        return $result;
    }

    public function authorizePayment($args = array()) {
        $client_token = ClientToken::generate($args);
        return array(
            'client_token' => $client_token
        );
    }

    private function proceedToPay($customer_id, $args = array()) {
        $transaction = array();
        $charge = Transaction::sale([
                    'customerId' => $customer_id,
                    'amount' => $args['price'],
                    'options' => [
                        'submitForSettlement' => true,
                        'threeDSecure' => [
                            "required" => false
                        ]
                    ]
        ]);
        if ($charge->success) {
            $transaction['success'] = true;
            $transaction['transaction_id'] = $charge->transaction->id;
            $transaction['transaction_amount'] = $charge->transaction->amount;
        } else {
            $transaction['success'] = false;
        }
        $transaction['transaction_status'] = $charge->transaction->status;
        $transaction['response_text'] = serialize($charge);
        return $transaction;
    }

}
