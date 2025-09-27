<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumerResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'full_name' => $this->full_name,
            'short_name' => $this->short_name,
            'address' => $this->address,
            'passport' => $this->passport,
            'formatted_passport' => $this->formatted_passport,
            'passport_issued_by' => $this->passport_issued_by,
            'passport_issued_date' => $this->passport_issued_date ? $this->passport_issued_date->format('Y-m-d') : null,
            'inn' => $this->inn,
            'formatted_inn' => $this->formatted_inn,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
            
            // Связанные данные
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
