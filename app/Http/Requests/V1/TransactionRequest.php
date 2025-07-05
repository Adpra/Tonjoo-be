<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

   public function rules(): array
    {
        $transactionId = $this->route('transaction');

        return [
            'description' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:100',
                $this->isMethod('POST')
                    ? 'unique:transaction_headers,code'
                    : Rule::unique('transaction_headers', 'code')->ignore($transactionId),
            ],
            'rate_euro' => 'required|numeric',
            'date_paid' => 'nullable|date',
            'categories' => 'required|array|min:1',
            'categories.*.category_id' => 'required|exists:ms_categories,id',
            'categories.*.transaction_details' => 'nullable|array',
            'categories.*.transaction_details.*.name' => 'required_with:categories.*.transaction_details|string',
            'categories.*.transaction_details.*.value_idr' => 'required_with:categories.*.transaction_details|numeric',
        ];
    }
}
