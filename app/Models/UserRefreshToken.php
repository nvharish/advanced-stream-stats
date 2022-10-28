<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRefreshToken extends Model {

    protected $table = 'user_refresh_tokens';
    protected $fillable = [
        'refresh_token',
        'user_id',
        'issued_at',
        'expire_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
