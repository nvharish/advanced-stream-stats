<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Guards;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

/**
 * Description of AccessTokenGuard
 *
 * @author haris
 */
class AccessTokenGuard implements Guard {

    use GuardHelpers;

    private $request;

    private const JWT_INPUT_KEY = 'jti';
    private const JWT_STORAGE_KEY = 'jti';

    public function __construct(UserProvider $provider, Request $request) {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user() {
        if (!is_null($this->user)) {
            return $this->user;
        }
        $key = env('APP_KEY');
        $jwt = $this->request->bearerToken();

        try {
            if (!is_null($jwt)) {
                $payload = JWT::decode($jwt, new Key($key, env('JWT_ALG')));
                //print_r($payload);exit;
                $user = $this->provider->retrieveByToken(self::JWT_STORAGE_KEY, $payload->{self::JWT_INPUT_KEY});
                $validated = !is_null($user) && $payload->issued_by == env('APP_URL') && ((!empty($payload->user) && $payload->user->id == $user->getAuthIdentifier()) || (!empty($payload->client) && $payload->client->id == $user->getAuthIdentifier()));
            }
        } catch (Throwable $ex) {
            return null;
        }

        if (!$validated) {
            return null;
        }
        $this->setUser($user);
        return $user;
    }

    public function validate(array $credentials = []): bool {
        if (!empty($credentials)) {
            $user = $this->provider->retrieveByCredentials($credentials);
            if (!is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
                $this->setUser($user);
                return true;
            }
        }
        return false;
    }

}
