<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract {

    private const REMEMBER_TOKEN_NAME = 'refresh_token';
    private const REFRESH_TOKEN_LENGTH = 128;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function accessToken() {
        return $this->hasOne(UserAccessToken::class);
    }

    public function refreshToken() {
        return $this->hasOne(UserRefreshToken::class);
    }

    public function findByCredentials(array $credentials) {
        $user = null;
        if (isset($credentials['grant_type']) && $credentials['grant_type'] == env('GRANT_REFRESH_TOKEN')) {
            $user = $this->with('refreshToken')
                    ->with('accessToken')
                    ->whereRelation('refreshToken', 'refresh_token', $credentials['refresh_token'])
                    ->whereRelation('refreshToken', 'expire_at', '>=', Service::currentDateTime())
                    ->first();
        } elseif (isset($credentials['grant_type']) && $credentials['grant_type'] == env('GRANT_PASSWORD')) {
            $user = $this->with('accessToken')
                    ->with('refreshToken')
                    ->where('username', $credentials['username'])
                    ->where('is_active', true)
                    ->first();
        }
        //print_r($user);exit;
        return $user;
    }

    public function findByToken($identifier, $token) {
        $user = $this->with('accessToken')
                ->whereRelation('accessToken', $identifier, $token)
                ->whereRelation('accessToken', 'expire_at', '>=', Service::currentDateTime())
                ->first();
        return $user;
    }

    public function getAuthIdentifier(): mixed {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthIdentifierName(): string {
        return $this->getKeyName();
    }

    public function getAuthPassword(): string {
        return $this->password;
    }

    public function getRememberToken(): string {
        return $this->refreshToken->{$this->getRememberTokenName()};
    }

    public function getRememberTokenName(): string {
        return self::REMEMBER_TOKEN_NAME;
    }

    public function setRememberToken($value): void {
        try {
            DB::beginTransaction();
            $this->accessToken()->updateOrCreate([
                'user_id' => $this->getAuthIdentifier(),
                    ], [
                'jti' => $value['jti'],
                'user_id' => $this->getAuthIdentifier(),
                'issued_at' => $value['issued_at'],
                'expire_at' => $value['access_token_expire_at'],
            ]);

            $this->refreshToken()->updateOrCreate([
                'user_id' => $this->getAuthIdentifier(),
                    ], [
                'refresh_token' => $value['refresh_token'],
                'user_id' => $this->getAuthIdentifier(),
                'issued_at' => $value['issued_at'],
                'expire_at' => $value['refresh_token_expire_at'],
            ]);
            DB::commit();
        } catch (Throwable $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function generateToken() {
        $access_token_update = $refresh_token_update = true;
        $payload = array();
        $payload['issued_by'] = env('APP_URL');
        $payload['issued_at'] = Service::currentDateTime();
        $payload['access_token_expire_at'] = Service::currentDateTime(env('DATETIME_FORMAT'), env('ACCESS_TOKEN_EXPIRE_INTERVAL'));
        $payload['jti'] = Service::generateUUID();
        $payload['refresh_token'] = Service::generateRandomString(self::REFRESH_TOKEN_LENGTH);
        $payload['refresh_token_expire_at'] = Service::currentDateTime(env('DATETIME_FORMAT'), env('REFRESH_TOKEN_EXPIRE_INTERVAL'));
        $payload['user'] = $this->withoutRelations()->toArray();

        if (!is_null($this->accessToken) && (Service::secondsBetween($this->accessToken->expire_at) - env('REFRESH_TIMEOUT_SECONDS')) > 0) {
            $payload['issued_at'] = $this->accessToken->issued_at;
            $payload['access_token_expire_at'] = $this->accessToken->expire_at;
            $payload['jti'] = $this->accessToken->jti;
            $payload['refresh_token'] = $this->refreshToken->refresh_token;
            $payload['refresh_token_expire_at'] = $this->refreshToken->expire_at;
            $access_token_update = false;
        }

        if (!is_null($this->refreshToken) && Service::secondsBetween($this->refreshToken->expire_at) > 0) {
            $payload['refresh_token'] = $this->refreshToken->refresh_token;
            $payload['refresh_token_expire_at'] = $this->refreshToken->expire_at;
            $refresh_token_update = false;
        }

        if ($access_token_update || $refresh_token_update) {
            $this->setRememberToken($payload);
        }

        //print_r($payload);exit;
        return array(
            'token_type' => env('TOKEN_TYPE'),
            'access_token' => JWT::encode($payload, env('APP_KEY'), env('JWT_ALG')),
            'refresh_token' => $payload['refresh_token'],
            'expires_in' => Service::secondsBetween($payload['access_token_expire_at']),
        );
    }

    public function destroyToken() {
        try {
            DB::beginTransaction();
            $this->accessToken()->delete();
            $this->refreshToken()->delete();
            DB::commit();
        } catch (Throwable $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

}
