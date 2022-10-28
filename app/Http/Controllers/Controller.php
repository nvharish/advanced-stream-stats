<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController {

    protected function generateResponse($data = null, $status = Response::HTTP_OK, $headers = array()) {
        if ($status != Response::HTTP_OK && $status != Response::HTTP_CREATED) {
            $data['status'] = 'ERROR';
            $data['message'] = $data['message'] ?? Response::$statusTexts[$status];
        }

        if (is_null($data)) {
            return response($data, $status, $headers)->header('Content-Type', 'application/json');
        }
        return response()->json($data, $status, $headers);
    }

}
