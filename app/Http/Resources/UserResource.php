<?php

namespace App\Http\Resources;

use App\Helpers\TimeHelper;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dataToSend = [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at ? TimeHelper::toLocalTime($this->created_at) : '',
        ];
        return $dataToSend;
    }
}
