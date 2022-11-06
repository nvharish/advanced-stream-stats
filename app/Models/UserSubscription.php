<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserSubscription extends Model {

    protected $table = 'user_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'plan_code',
        'amount',
        'currency',
        'start_at',
        'end_at',
        'payment_method_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public static function saveUserSubscription($user_subscription = array(), $transaction = array()) {
        try {
            DB::beginTransaction();
            if ($transaction['save_payment_method']) {
                $payment_method = UserPaymentMethod::create([
                            'user_id' => $transaction['user_id'],
                            'customer_id' => $transaction['gateway_response']->customerDetails->id,
                            'payment_method_token' => (isset($transaction['paypal']) && $transaction['paypal']) ? $transaction['gateway_response']->paypalDetails->implicitlyVaultedPaymentMethodToken : $transaction['gateway_response']->creditCardDetails->token,
                            'payment_method_mask' => isset($transaction['gateway_response']->creditCardDetails->maskedNumber) ? $transaction['gateway_response']->creditCardDetails->maskedNumber : '',
                ]);
                $user_subscription['payment_method_id'] = $payment_method->id;
            }
            //print_r($user_subscription);exit;
            $user_sub = UserSubscription::create($user_subscription);
            $transaction['user_subscription_id'] = $user_sub->id;
            $transaction['gateway_response'] = serialize($transaction['gateway_response']);
            UserTransaction::create($transaction);
            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public static function cancelUserSubscription($user_id) {
        try {
            $user_subscription = UserSubscription::where('user_id', $user_id)->first();
            if (!is_null($user_subscription)) {
                $user_subscription->is_active = false;
                $user_subscription->cancel_date = gmdate(env('DATETIME_FORMAT'));
                $user_subscription->save();
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public static function getActiveSubscription($user_id) {
        $subscription = UserSubscription::where('user_id', $user_id)
                        ->where(function ($q) {
                            $q->where('is_active', true);
                            $q->orWhere('end_at', '>=', gmdate(env('DATETIME_FORMAT')));
                        })->first()->toArray();
        return $subscription;
    }

}
