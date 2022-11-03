<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\GatewayWrappers\BrainTree;

class PaymentService {

    private const SUBSCRIPTION_PLANS = array(
        'silver' => [
            'currency' => 'USD',
            'price' => '199'
        ],
        'gold' => [
            'currency' => 'USD',
            'price' => '399'
        ],
    );

    private $braintree_wrapper;

    public function __construct(BrainTree $braintree_wrapper) {
        $this->braintree_wrapper = $braintree_wrapper;
    }

    public function purchaseSubscription($args = array()) {
        $params = self::SUBSCRIPTION_PLANS[$args['plan_code']];
        $params['email'] = 'admin_harrysoftechhub.com@yopmail.com';
        $params['first_name'] = 'Admin';
        $params['last_name'] = 'HarrySoftechHub';
        $params['payment_method_nonce'] = $args['payment_method_nonce'];
        $params['orderId'] = $args['orderId'];
        //print_r($params);exit;
        if ($args['is_paypal']) {
            $result = $this->braintree_wrapper->doPayPalPayment($params);
        } else {
            $result = $this->braintree_wrapper->doPayment($params);
        }

        return $result;
    }

    public function authorizePayment($args = array()) {
        $result = $this->braintree_wrapper->authorizePayment($args);
        return $result;
    }

}
