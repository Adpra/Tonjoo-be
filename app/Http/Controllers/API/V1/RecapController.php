<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TransactionRecapResource;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RecapController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->per_page ?? 10);
        $page = (int) ($request->page ?? 1);

        try {
            $query = TransactionDetail::with(['transaction', 'category']);

           if ($request->filled('search')) {
                $keyword = strtolower($request->search);
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->whereRaw('LOWER(name) like ?', ["%$keyword%"]);
                });
            }

            if ($request->filled('start_date')) {
                $query->whereHas('transaction', function ($q) use ($request) {
                    $q->whereDate('date_paid', '>=', $request->start_date);
                });
            }

            if ($request->filled('end_date')) {
                $query->whereHas('transaction', function ($q) use ($request) {
                    $q->whereDate('date_paid', '<=', $request->end_date);
                });
            }

            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->whereRaw('LOWER(name) like ?', ["%" . strtolower($request->category) . "%"]);
                });
            }

            $details = $query->get();

            $grouped = $details->groupBy(function ($item) {
                return $item->transaction->date_paid->format('Y-m-d') . '|' . $item->category->name;
            });

            $recapCollection = collect();

            foreach ($grouped as $key => $group) {
                [$date, $category] = explode('|', $key);
                $recapCollection->push([
                    'date_paid' => $date,
                    'category' => $category,
                    'total_nominal' => $group->sum('value_idr'),
                ]);
            }

            $total = $recapCollection->count();
            $results = $recapCollection->slice(($page - 1) * $perPage, $perPage)->values();

            return TransactionRecapResource::collection($results)
                ->additional([
                    'code' => Response::HTTP_OK,
                    'success' => true,
                    'message' => __('messages.data_list'),
                    'meta' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => ceil($total / $perPage),
                    ],
                ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get transaction recap',
                'error' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
