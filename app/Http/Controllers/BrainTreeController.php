<?php

namespace App\Http\Controllers;

use App\Services\BrainTreeService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class BrainTreeController extends Controller {

    private $braintree_service;

    public function __construct(BrainTreeService $braintree_service) {
        $this->braintree_service = $braintree_service;
    }

    public function generateClientToken() {
        $client_token = $this->braintree_service->generateClientToken();
        if (is_null($client_token)) {
            $response = $this->generateResponse($client_token, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $response = $this->generateResponse(['client_token' => $client_token], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function makeTransaction(Request $request) {
        $params = $request->all();
        
    }

}
