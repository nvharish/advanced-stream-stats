<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentMethod extends Model {

    protected $table = 'user_payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'customer_id',
        'payment_method_token',
        'payment_method_mask',
        'is_default'
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
    
    public static function getUserPaymentMethods($user_id) {
        return UserPaymentMethod::where('user_id', $user_id)->get();
    }

}
