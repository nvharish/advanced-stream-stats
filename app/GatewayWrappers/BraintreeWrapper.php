<?php

namespace App\GatewayWrappers;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\Exception;
use Braintree\Transaction;

class BraintreeWrapper {

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

    public function generateClientToken() {
        try {
            $client_token = ClientToken::generate();
        } catch (Exception $ex) {
            $client_token = null;
        }
        return $client_token;
    }

    public function getPaymentMethod() {
        $pay = \Braintree\PaymentMethod::find('mpyk9vqq');
        print_r($pay);
        exit;
    }

    public function doTransaction($args = array()) {
        $result = array();
        try {
            $params = array(
                'amount' => $args['amount'],
                //'description' => $args['description'],
                'options' => [
                    'submitForSettlement' => true,
                ]
            );

            if (isset($args['payment_method_nonce']) && !empty($args['payment_method_nonce'])) {
                $params['paymentMethodNonce'] = $args['payment_method_nonce'];
                $params['options']['storeInVaultOnSuccess'] = true;
                $params['options']['threeDSecure'] = [
                    'required' => true
                ];
            }

            if (isset($args['paypal']) && $args['paypal']) {
                $params['orderId'] = $args["order_id"];
                $params['options']['paypal'] = array(
                    'description' => $args['plan_code'] . $args['amount'],
                );
            }

            if (isset($args['payment_method_token']) && !empty($args['payment_method_token'])) {
                $params['paymentMethodToken'] = $args['payment_method_token'];
            } elseif ((isset($args['customer_id']) && empty($args['customer_id'])) || !isset($args['customer_id'])) {
                $params['customer'] = array(
                    'email' => $args['email'],
                    'firstName' => $args['first_name'],
                    'lastName' => $args['last_name'],
                );
            } else {
                $params['customerId'] = $args['customer_id'];
            }
            $transaction_result = Transaction::sale($params);
            $result['success'] = $transaction_result->success;
            $result['transaction_reference'] = $transaction_result->transaction->id ?? '';
            $result['amount'] = $transaction_result->transaction->amount ?? $args['amount'];
            $result['gateway_response'] = $transaction_result->transaction;
            $result['message'] = $transaction_result->transaction->message ?? '';
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
        return $result;
    }

}
