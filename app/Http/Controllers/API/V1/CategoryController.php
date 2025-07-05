<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\MsCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_list');

        try {
            $categories = MsCategory::query()
                ->latest('created_at')
                ->get();

            return CategoryResource::collection($categories)
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

}
