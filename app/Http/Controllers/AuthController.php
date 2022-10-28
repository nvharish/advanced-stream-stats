<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller {

    private $auth_service;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthService $auth_service) {
        $this->auth_service = $auth_service;

        $this->middleware('auth:user', [
            'only' => [
                'destroyToken',
            ]
        ]);

        $this->middleware('auth:fe_client', [
            'only' => [
                'createToken',
            ]
        ]);
    }

    public function createToken(Request $request) {
        $this->validate($request, array(
            'grant_type' => 'required|in:' . env('GRANT_PASSWORD') . ',' . env('GRANT_REFRESH_TOKEN'),
        ));
        $credentials = $request->all();
        //print_r($credentials);exit;

        if ($credentials['grant_type'] == env('GRANT_PASSWORD')) {
            $this->validate($request, array(
                'username' => 'required',
                'password' => 'required',
            ));
        } elseif ($credentials['grant_type'] == env('GRANT_REFRESH_TOKEN')) {
            $this->validate($request, array(
                'refresh_token' => 'required',
            ));
        }
        //print_r($credentials);exit;
        $token = $this->auth_service->validateByCredentials($credentials);

        if ($token == null) {
            $response = $this->generateResponse(null, Response::HTTP_UNAUTHORIZED);
        } else {
            $response = $this->generateResponse($token);
        }
        return $response;
    }

    public function destroyToken() {
        try {
            $this->auth_service->destroyToken();
            $response = $this->generateResponse();
        } catch (Throwable $ex) {
            //print_r($ex);
            $response = $this->generateResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $response;
    }

}
