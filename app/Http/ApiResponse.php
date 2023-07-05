<?php

namespace App\Http;

use App\Http\Resources\ApiResponseResource;

class ApiResponse
{
    public static function success($data = null, $message = null, $code = 200)
    {
        return self::generateResponse([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'code' => 200,
        ],  $code);
    }

    public static function error($message = null, $code = 500, $data = null)
    {
        return self::generateResponse([
            'success' => false,
            'message' => $message,
            'error' => $data,
            'code' => $code,
        ],  $code);
    }

    private static function generateResponse($data, $code)
    {
        return (new ApiResponseResource($data))->response()->setStatusCode($code);
    }
}
