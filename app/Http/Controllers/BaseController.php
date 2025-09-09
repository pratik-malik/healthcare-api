<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    /**
     * success response method.
     * @return JsonResponse
     *
     */
    public function sendResponse($result, $message, $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $result,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, 200);
    }

    /**
     * return error response.
     * @return JsonResponse
     *
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
