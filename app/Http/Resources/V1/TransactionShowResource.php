<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionShowResource extends JsonResource
{
   public function toArray(Request $request): array
    {
        $details = $this->details->load('category');

        $categories = $details->sortBy('group')->groupBy('group');

        return [
            'id' => $this->id,
            'description' => $this->description,
            'code' => $this->code,
            'rate_euro' => $this->rate_euro,
            'date_paid' => optional($this->date_paid)->format('Y-m-d'),
            'categories' => $categories->map(function ($details) {
                $category = $details->first()->category;

                return [
                    'category_id' => $category->id,
                    'name' => $category->name,
                    'transaction_details' => $details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'name' => $detail->name,
                            'value_idr' => $detail->value_idr,
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

}
