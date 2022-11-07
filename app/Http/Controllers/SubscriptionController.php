<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller {

    private $payment_service;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PaymentService $payment_service) {
        $this->payment_service = $payment_service;

        $this->middleware('auth:user', [
            'except' => [
                'renew',
        ]]);
    }

    public function authorizePayment() {
        //$params = $request->all();
        $result = $this->payment_service->authorizePayment();
        if (!empty($result)) {
            $response = $this->generateResponse(['client_token' => $result]);
        } else {
            $response = $this->generateResponse($result, Response::HTTP_UNAUTHORIZED);
        }

        return $response;
    }

    public function purchase(Request $request) {
        $this->validate($request, array(
            'plan_code' => 'required|in:silver,gold',
            'payment_method_nonce' => 'required'
        ));
        $params = $request->all();
        //print_r($params);exit;
        $result = $this->payment_service->purchaseSubscription($params);
        $response = $this->generateResponse($result);
        return $response;
    }

    public function cancel() {
        $this->payment_service->cancelSubscription();
    }

    public function renew() {
        $this->payment_service->renewSubscriptions();
    }

}
