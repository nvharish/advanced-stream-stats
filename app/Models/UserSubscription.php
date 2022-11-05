<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'start_date',
        'end_date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function userPaymentMethod() {
        return $this->belongsTo(UserSubscription::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public static function saveUserSubscription($user_subscription = array(), $transaction = array()) {
        try {
            DB::beginTransaction();
            $payment_method = UserPaymentMethod::createOrUpdate([
                        'customer_id' => $transaction->customer->id,
                        'payment_method_name' => $transaction->creditCard->token,
                        'payment_method_mask' => isset($transaction->creditCardDetails->maskedNumber) ? $transaction->creditCardDetails->maskedNumber : '',
            ]);
            $user_subscription['payment_method_id'] = $payment_method->id;
            $user_sub = UserSubscription::createOrUpdate($user_subscription);
            $transaction['user_subscription_id'] = $user_sub->id;
            Transaction::create($transaction);
            DB::commit();
        } catch (QueryException $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

    public static function cancelUserSubscription($subscription_id) {
        try {
            $user_subscription = UserSubscription::find($subscription_id);
            if (!is_null($user_subscription)) {
                $user_subscription->is_active = false;
                $user_subscription->cancel_date = gmdate(env('DATETIME_FORMAT'));
                $user_subscription->save();
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
