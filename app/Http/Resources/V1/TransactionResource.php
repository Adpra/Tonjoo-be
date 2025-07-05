<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $transactionDetail = $this;
        return [
            'id' => $transactionDetail->id,
            'name' => $transactionDetail->name,
            'value_idr' => $transactionDetail->value_idr,
            'transaction_id' => $transactionDetail?->transaction?->id,
            'description' => optional($transactionDetail->transaction)->description,
            'code' =>  $transactionDetail?->transaction?->code,
            'rate_euro' => $transactionDetail?->transaction?->rate_euro,
            'date_paid' => optional($transactionDetail->transaction)?->date_paid?->format('Y-m-d'),
            'category' =>  $transactionDetail?->category?->name

        ];
    }
}
