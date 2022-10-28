<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessToken extends Model {

    protected $table = 'user_access_tokens';
    protected $fillable = [
        'jti',
        'user_id',
        'issued_at',
        'expire_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
