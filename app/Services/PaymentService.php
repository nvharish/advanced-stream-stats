<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\GatewayWrappers\BraintreeWrapper;
use App\Models\UserSubscription;
use App\Models\UserPaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Models\UserTransaction;

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
                ])->select(['customer_id'])->first();
        if (isset($args['payment_method_id']) && !empty($args['payment_method_id'])) {
            $payment_method = UserPaymentMethod::where([
                        'id' => $args['payment_method_id']
                    ])->select(['payment_method_token', 'customer_id'])->first();
        };
        //print_r($payment_method->isEmpty());exit;
        if (!is_null($payment_method)) {
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
                'start_at' => gmdate($format),
                'end_at' => gmdate($format, strtotime($duration))
            );
            $transaction = array(
                'user_id' => $user->id,
                'transaction_reference' => $result['transaction_reference'],
                'amount' => $result['amount'],
                'currency' => $args['currency'],
                'save_payment_method' => !(isset($args['payment_method_id']) && !empty($args['payment_method_id'])),
                'gateway_response' => $result['gateway_response'],
                'paypal' => isset($args['paypal']) && $args['paypal']
            );
            UserSubscription::saveUserSubscription($user_subscription, $transaction);
        }
        return $result;
    }

    public function authorizePayment() {
        $result = $this->braintree_wrapper->generateClientToken();
        return $result;
    }

    public function cancelSubscription() {
        $user = Auth::user();
        UserSubscription::cancelUserSubscription($user->id);
    }

    public function renewSubscriptions() {
        $user_subscriptions = UserSubscription::join('user_payment_methods', 'user_payment_methods.id', '=', 'user_subscriptions.payment_method_id')
                        ->where('is_active', true)
                        ->where('end_at', '<=', gmdate(env('DATETIME_FORMAT')))->limit(100)
                        ->select([
                            'user_subscriptions.user_id as user_id',
                            'start_at',
                            'end_at',
                            'user_subscriptions.id as id',
                            'payment_method_token',
                            'amount',
                            'currency',
                            'plan_code'
                        ])->get();
        //print_r($user_subscriptions);exit;
        try {
            foreach ($user_subscriptions as $user_subscription) {
                $amount = $user_subscription->amount;
                $payment_method_token = $user_subscription->payment_method_token;
                $result = $this->braintree_wrapper->doTransaction([
                    'amount' => $amount,
                    'payment_method_token' => $payment_method_token
                ]);
                //print_r($result);exit;
                if ($result['success']) {
                    $format = env('DATETIME_FORMAT');
                    $duration = self::SUBSCRIPTION_PLANS[$user_subscription->plan_code]['duration'];
                    $start_at = gmdate($format);
                    $end_at = gmdate($format, strtotime($duration));

                    $user_subscription->start_at = $start_at;
                    $user_subscription->end_at = $end_at;

                    $transaction = new UserTransaction();
                    $transaction->user_id = $user_subscription->user_id;
                    $transaction->transaction_reference = $result['transaction_reference'];
                    $transaction->amount = $result['amount'];
                    $transaction->currency = $user_subscription->currency;
                    $transaction->gateway_response = serialize($result['gateway_response']);
                    $transaction->user_subscription_id = $user_subscription->id;

                    DB::beginTransaction();
                    $user_subscription->update();
                    $transaction->save();
                    DB::commit();
                }
            }
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
