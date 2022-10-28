<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService {

    public function validateByCredentials($credentials = array()) {
        $token = null;
        $guard = Auth::guard();

        if (($credentials['grant_type'] == env('GRANT_CLIENT_CREDENTIALS') && $guard->check()) || $guard->validate($credentials)) {
            $user = $guard->user();
            $token = $user->generateToken();
        }

        return $token;
    }

    public function destroyToken() {
        $user = Auth::user();
        //print_r($user);exit;
        try {
            $user->destroyToken();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
