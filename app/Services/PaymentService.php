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
        $args['email'] = 'admin_harrysoftechhub.com@yopmail.com';
        $this->braintree_wrapper->doPayment($args);
    }

    public function authorizePayment($args = array()) {
        $result= $this->braintree_wrapper->authorizePayment($args);
        return $result;
    }

}
