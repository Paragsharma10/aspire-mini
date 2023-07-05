<?php

namespace App\Http\Requests;

class GetLoanRequest extends CustomFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'id' => 'required|numeric|exists:loans,id',
        ];
    }
}
