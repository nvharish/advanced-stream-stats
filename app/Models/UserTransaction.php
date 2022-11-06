<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTransaction extends Model {

    protected $table = 'user_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'gateway_response',
        'user_subscription_id',
        'transaction_reference',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function userSubscription() {
        return $this->belongsTo(UserSubscription::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
