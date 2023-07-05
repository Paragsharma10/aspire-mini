<?php

namespace App\Http\Requests;

class StoreLoanRequest extends CustomFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'term' => 'required|numeric|min:1',
        ];
    }
}
