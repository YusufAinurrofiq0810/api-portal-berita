<?php

namespace App\Http\Controllers;

use app\Helpers\Helper;
use App\Http\Requests\Auth\LoginValidate;
use App\Http\Requests\Auth\RegisterValidate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthController extends Controller
{
    public function register(RegisterValidate $request): JsonResponse
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return Helper::sendSuccessResponse(['new_user' => $user, 'token' => $token], Response::HTTP_CREATED, 'Registration successful');
        } catch (\Exception $e) {
            DB::rollBack();
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function login(LoginValidate $request): JsonResponse
    {
        $data = $request->validated();
        try {
            if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                throw new BadRequestException('email or password invalid');
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return Helper::sendSuccessResponse(['user' => $user, 'token' => $token], Response::HTTP_OK, 'Login success');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($e instanceof BadRequestException) {
                $statusCode = Response::HTTP_BAD_REQUEST;
            }
            return Helper::sendErrorResponse(['file' => $e->getFile(), 'line' => $e->getLine()], $statusCode, $e->getMessage());
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();
            return Helper::sendSuccessResponse(['logout_user' => $user], Response::HTTP_OK, 'Logout Success');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }

    public function profile(): JsonResponse
    {
        try {
            $user = Auth::user();
            return Helper::sendSuccessResponse(['profile' => $user], Response::HTTP_OK, 'Get Profile Success');
        } catch (\Exception $e) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            return Helper::sendErrorResponse(
                ['file' => $e->getFile(), 'line' => $e->getLine()],
                $statusCode,
                $e->getMessage()
            );
        }
    }
}
