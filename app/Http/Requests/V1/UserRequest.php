<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (request()->isMethod('POST')) {
            $rules = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'role_id' => 'required|exists:roles,id',
            ];
        }

        if (request()->isMethod('PUT')) {
            $userId = request()->segment(count(request()->segments()));

            $rules = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $userId . ',id',
                'password' => 'required|min:8|confirmed',
                'role_id' => 'required|exists:roles,id',
            ];
        }

        return $rules;
    }
}
