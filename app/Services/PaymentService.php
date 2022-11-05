<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\GatewayWrappers\BraintreeWrapper;
use App\Models\UserSubscription;
use App\Models\UserPaymentMethod;

class PaymentService {

    private const SUBSCRIPTION_PLANS = array(
        'silver' => [
            'currency' => 'USD',
            'amount' => '199',
            'duration' => '1 month',
        ],
        'gold' => [
            'currency' => 'USD',
            'amount' => '399',
            'duration' => '1 year',
        ],
    );

    private $braintree_wrapper;

    public function __construct(BraintreeWrapper $braintree_wrapper) {
        $this->braintree_wrapper = $braintree_wrapper;
    }

    public function purchaseSubscription($args = array()) {
        $args['amount'] = self::SUBSCRIPTION_PLANS[$args['plan_code']]['amount'];
        $args['currency'] = self::SUBSCRIPTION_PLANS[$args['plan_code']]['currency'];
        $user = Auth::user();
        $args['email'] = $user->username . '@harrysoftechhub.com';
        $args['first_name'] = $user->name;
        $args['last_name'] = $user->name;
        //print_r($args);exit;
        //$this->braintree_wrapper->getPaymentMethod();exit;
        $payment_method = UserPaymentMethod::where([
                    'user_id' => $user->id
                ])->select(['customer_id'])->limit(1)->get();
        if (isset($args['payment_method_id']) && !empty($args['payment_method_id'])) {
            $payment_method = UserPaymentMethod::where([
                        'id' => $args['payment_method_id']
                    ])->select(['payment_method_token', 'customer_id'])->get();
        };
        if (!empty($payment_method)) {
            $args['customer_id'] = $payment_method->customer_id;
            $args['payment_method_token'] = isset($payment_method->payment_method_token) ? $payment_method->payment_method_token : null;
        }
        $result = $this->braintree_wrapper->doTransaction($args);
        if ($result['success']) {
            $format = env('DATETIME_FORMAT');
            $duration = self::SUBSCRIPTION_PLANS[$args['plan_code']]['duration'];
            $user_subscription = array(
                'user_id' => $user->id,
                'plan_code' => $args['plan_code'],
                'amount' => $result['amount'],
                'currency' => $args['currency'],
                'start_date' => gmdate($format),
                'end_date' => gmdate($format, strtotime($duration))
            );
            $transaction = array(
                'user_id' => $user->id,
                'transaction_reference' => $result['transaction_reference'],
                'amount' => $result['amount'],
                'currency' => $args['currency'],
                'gateway_response' => $result['gateway_response']
            );
            UserSubscription::saveUserSubscription($user_subscription, $transaction);
        }
        return $result;
    }

    public function authorizePayment() {
        $result = $this->braintree_wrapper->generateClientToken();
        return $result;
    }

}
