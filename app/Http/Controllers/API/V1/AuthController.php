<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\RoleIdEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Requests\V1\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class AuthController extends Controller
{
     /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:api',
            [
                'except' => [
                    'login',
                    'register'
                ]
            ]
        );
    }

    public function register(RegisterRequest $request)
    {
        $success = true;
        $message = __('auth.authenticated');

        try {

            $user = User::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'role_id' => RoleIdEnum::USER,
                ]
            );

            $credentials = request(['email', 'password']);

            $token = auth('api')->attempt($credentials);

            return response()->json(
                [
                    'success' => $success,
                    'message' => $message,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL(),
                    'user' => $user,
                ]
            );
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'success' => false,
                    'message' => $th->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $success = true;
        $message = __('auth.authenticated');

        try {
            $user = User::query()
                ->where('email', $request->email)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.user_not_found')
                ], Response::HTTP_NOT_FOUND);
            }

            if (!password_verify($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.password'),
                ], Response::HTTP_UNAUTHORIZED);
            }

            $credentials = request(['email', 'password']);

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.failed'),
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json(
                [
                    'success' => $success,
                    'message' => $message,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL(),
                    'user' => auth('api')->user(),
                ]
            );
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'success' => false,
                    'message' => $th->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $success = true;
        $message = __('message.data_displayed');

        try {
            $userId = auth('api')->id();
            $token = auth('api')->tokenById($userId);

            $user = auth('api')->user()->load('role');
            return response()->json(
                [
                    'success' => $success,
                    'message' => $message,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL(),
                    'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'role' => $user->role->name,
                    ],
                ]
            );
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $th->getMessage(),
                ]
            );
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $success = true;
        $message = __('auth.user_logout');

        try {
            auth('api')->logout(true);
        } catch (\Throwable $th) {
            $success = false;
            $message = $th->getMessage();
        }

        return response()->json(
            [
                'success' => $success,
                'message' => $message,
            ]
        );
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
