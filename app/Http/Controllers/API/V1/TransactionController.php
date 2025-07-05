<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TransactionRequest;
use App\Http\Resources\V1\TransactionResource;
use App\Http\Resources\V1\TransactionShowResource;
use App\Models\MsCategory;
use App\Models\TransactionDetail;
use App\Models\TransactionHeader;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        try {
            $query = TransactionDetail::query()
                ->select('transaction_details.*')
                ->with(['transaction', 'category'])
                ->join('transaction_headers', 'transaction_details.transaction_id', '=', 'transaction_headers.id');

            // Search
            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('transaction_headers.description', 'ilike', "%$keyword%")
                    ->orWhere('transaction_headers.code', 'ilike', "%$keyword%")
                    ->orWhere('transaction_details.name', 'ilike', "%$keyword%");
                });
            }

            // Filter date range
            if ($request->filled('start_date')) {
                $query->whereDate('transaction_headers.date_paid', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('transaction_headers.date_paid', '<=', $request->end_date);
            }

            // Filter category
            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', 'ilike', '%' . $request->category . '%');
                });
            }

            // Sort
            if ($request->filled('sort_by')) {
                $query->orderBy('transaction_details.created_at', $request->sort_by);
            }

            $query->latest('transaction_details.created_at');

            $transactions = $query->paginate($perPage, ['*'], 'page', $page);

            return TransactionResource::collection($transactions)
                ->additional([
                    'code' => 200,
                    'success' => true,
                    'message' => __('messages.data_list'),
                ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
   public function store(TransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            $transaction = TransactionHeader::create([
                'description' => $request->description,
                'code' => $request->code,
                'date_paid' => $request->date_paid,
                'rate_euro' => $request->rate_euro,
            ]);

            foreach ($request->categories as $ctIdx => $category) {
                foreach ($category['transaction_details'] as $detail) {
                    TransactionDetail::query()->create([
                        'name' => $detail['name'],
                        'value_idr' => $detail['value_idr'],
                       'transaction_category_id' => $category['category_id'],
                        'transaction_id' => $transaction->id,
                        'group' => $ctIdx +1
                    ]);
                }
            }

            DB::commit();

            return TransactionResource::make($transaction)->additional([
                'code' => Response::HTTP_CREATED,
                'success' => true,
                'message' => __('messages.data_saved'),
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save transaction.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_displayed');
        $transactionHeader = TransactionHeader::findOrFail($id);
        $this->authorize('view', $transactionHeader);

        try {
            return TransactionShowResource::make($transactionHeader)
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
     * Update the specified resource in storage.
     */
   public function update(TransactionRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $transaction = TransactionHeader::findOrFail($id);
            $this->authorize('update', $transaction);

            $transaction->update([
                'description' => $request->description,
                'code' => $request->code,
                'date_paid' => $request->date_paid,
                'rate_euro' => $request->rate_euro,
            ]);

            $existingDetailIds = [];

            foreach ($request->categories as $ctIdx => $categoryGroup) {
                foreach ($categoryGroup['transaction_details'] as $detail) {
                    if (!empty($detail['id']) && is_numeric($detail['id'])) {
                        $existing = TransactionDetail::where('id', $detail['id'])
                            ->where('transaction_id', $transaction->id)
                            ->first();

                        if ($existing) {
                            $existing->update([
                                'name' => $detail['name'],
                                'value_idr' => $detail['value_idr'],
                                'transaction_category_id' => $categoryGroup['category_id'],
                                'group' => $ctIdx + 1
                            ]);
                            $existingDetailIds[] = $existing->id;
                        }
                    } else {
                        $newDetail = TransactionDetail::create([
                            'transaction_id' => $transaction->id,
                            'transaction_category_id' => $categoryGroup['category_id'],
                            'name' => $detail['name'],
                            'value_idr' => $detail['value_idr'],
                            'group' => $ctIdx + 1
                        ]);
                        $existingDetailIds[] = $newDetail->id;
                    }
                }
            }

            $transaction->details()
                ->whereNotIn('id', $existingDetailIds)
                ->delete();

            DB::commit();

            return TransactionShowResource::make($transaction)->additional([
                'code' => Response::HTTP_OK,
                'success' => true,
                'message' => 'Transaction updated successfully.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $code = Response::HTTP_OK;
        $success = true;
        $message = __('messages.data_deleted');

        $transactionDetail = TransactionDetail::findOrFail($id);
        $this->authorize('delete', $transactionDetail->transaction);

        try {
            $transactionDetail->delete();
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
