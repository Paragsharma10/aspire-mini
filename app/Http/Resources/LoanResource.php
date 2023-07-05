<?php

namespace App\Http\Resources;

use App\Helpers\TimeHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'term' => $this->term,
            'created_at' => $this->created_at ? TimeHelper::toLocalTime($this->created_at) : '',
            'user' => $this->user ? new UserResource($this->user) : '',
            'repayment' => $this->loanDetails ? LoanRepaymentResource::collection($this->loanDetails) : '',
        ];
    }
}
