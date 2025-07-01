<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\RoleIdEnum;
use App\Helpers\MediaHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

     public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_list');
        $perPage = $request->per_page ?? 15;
        $page = $request->page ?? 1;


        try {
            $users = User::query()
                ->latest('created_at');

            $users = $users->paginate($perPage, ['*'], 'page', $page);

            return UserResource::collection($users)
                ->additional(
                    [
                        'code' => $code,
                        'success' => $success,
                        'message' => $message,
                    ]
                );
        } catch (\Throwable $th) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

            return response()->json(
                [
                    'code' => $code,
                    'success' => false,
                    'message' => $th->getMessage(),
                ],
                $code
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $code = Response::HTTP_CREATED;
        $success = true;
        $message = __('messages.data_saved');

        try {
            $user = User::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'image' => MediaHelper::handleUploadImage($request->image),
                    'role_id' => $request->role_id ?? RoleIdEnum::USER,
                ]
            );

            return UserResource::make($user)
                ->additional(
                    [
                        'code' => $code,
                        'success' => $success,
                        'message' => $message,
                    ]
                );
        } catch (\Throwable $th) {

            Log::error($th->getMessage());
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

            return response()->json(
                [
                    'success' => false,
                    'message' => $th->getMessage(),
                ],
                $code
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_displayed');

        try {

            return UserResource::make($user)
                ->additional([
                    'code' => $code,
                    'success' => $success,
                    'message' => $message,
                ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

            return response()->json(
                [
                    'success' => false,
                    'message' => $th->getMessage()
                ],
                $code
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_saved');

        try {

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'image' => MediaHelper::handleUploadImage(image: $request->image, oldFile: $user->image),
                'role_id' => $request->role_id ?? RoleIdEnum::USER,
            ]);

            return UserResource::make($user)
                ->additional([
                    'code' => $code,
                    'success' => $success,
                    'message' => $message,
                ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], $code);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_deleted');

        try {
            $user->delete();
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $success = false;
            $message = $th->getMessage();
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ], $code);
    }
}
