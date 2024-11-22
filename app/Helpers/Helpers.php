<?php

namespace app\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class Helper
{
    public static function sendSuccessResponse($result, $statusCode = Response::HTTP_OK,  string $message = 'Berhasil mengambil data', $header = []): JsonResponse
    {
        return response()->json([
            'status' => 'SUCCESS',
            'status_code' => $statusCode,
            'message' => $message,
            'result' => $result
        ], $statusCode, $header);
    }

    public static function sendErrorResponse($errors, $statusCode = Response::HTTP_BAD_REQUEST, string $message = 'Something went wrong', $header = []): JsonResponse
    {
        $env = env('APP_ENV');
        $body_errors = [
            'status' => 'ERROR',
            'status_code' => $statusCode,
            'message' => $message,
        ];
        $body_errors['errors'] = $env == 'production' ? null : $errors;
        return response()->json($body_errors, $statusCode, $header);
    }
}
