<?php

namespace App\Http\Resources;

use App\Helpers\TimeHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanRepaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'repayment_amount' => $this->repayment_amount,
            'repayment_time' => $this->repayment_time ? Carbon::createFromDate($this->repayment_time) : '',
        ];
    }
}
