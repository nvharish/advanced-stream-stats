<?php

namespace App\GatewayWrappers;

use Braintree\Customer;
use Braintree\ClientToken;
use Braintree\Configuration;

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
        
    }

    public function authorizePayment($args = array()) {
        $client_token = ClientToken::generate($args);
        return array(
            'client_token' => $client_token
        );
    }

    private function createCustomer($args = array()) {
        Customer::create([
            'email' => $args['email'],
            'creditCard' => [
                'cardholderName' => $args['card_holder_name'],
                'cvv' => $args['cvv'],
                'expirationMonth' => $args['exp_month'],
                'expirationYear' => $args['exp_year'],
                'number' => $args['card_number']
            ]
        ]);
    }

}
