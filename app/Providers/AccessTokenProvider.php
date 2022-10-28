<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Services\Service;

/**
 * Description of AccessTokenProvider
 *
 * @author haris
 */
class AccessTokenProvider implements UserProvider {

    private $model;

    public function __construct(Authenticatable $model) {
        $this->model = $model;
    }

    public function retrieveByToken($identifier, $token) {
        return $this->model->findByToken($identifier, $token);
    }

    public function retrieveByCredentials(array $credentials) {
        return $this->model->findByCredentials($credentials);
    }

    public function retrieveById($identifier) {
        return $this->model->find($identifier);
    }

    public function updateRememberToken(Authenticatable $user, $token): void {
        $user->setRememberToken($token);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool {
        if (isset($credentials['grant_type']) && $credentials['grant_type'] == env('GRANT_PASSWORD')) {
            $password = $credentials['password'];
        } elseif (isset($credentials['grant_type']) && $credentials['grant_type'] == env('GRANT_REFRESH_TOKEN')) {
            return true;
        } else {
            $password = $credentials['client_secret'];
        }
        return Service::verifyHash($password, $user->getAuthPassword());
    }

}
