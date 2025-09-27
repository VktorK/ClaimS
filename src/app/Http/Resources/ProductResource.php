<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'date_of_buying' => $this->date_of_buying ? $this->date_of_buying->format('Y-m-d') : null,
            'warranty_period' => $this->warranty_period,
            'formatted_warranty_period' => $this->formatted_warranty_period,
            'age_in_days' => $this->age_in_days,
            'has_serial_number' => $this->hasSerialNumber(),
            'claims_count' => $this->claims_count,
            'active_claims_count' => $this->active_claims_count,
            'seller_id' => $this->seller_id,
            'claim_id' => $this->claim_id,
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
            
            'seller' => $this->whenLoaded('seller', function () {
                return [
                    'id' => $this->seller->id,
                    'title' => $this->seller->title,
                    'address' => $this->seller->address,
                    'ogrn' => $this->seller->ogrn,
                ];
            }),
            
            'claim' => $this->whenLoaded('claim', function () {
                return [
                    'id' => $this->claim->id,
                    // Поля claim будут добавлены когда создадим модель Claim
                ];
            }),
            
            'claims' => $this->whenLoaded('claims', function () {
                return $this->claims->map(function ($claim) {
                    return [
                        'id' => $claim->id,
                        'title' => $claim->title,
                        'status' => $claim->status,
                        'status_label' => $claim->status_label,
                        'type' => $claim->type,
                        'type_label' => $claim->type_label,
                        'claim_date' => $claim->claim_date ? $claim->claim_date->format('Y-m-d') : null,
                        'claimed_amount' => $claim->claimed_amount,
                        'formatted_claimed_amount' => $claim->formatted_claimed_amount,
                    ];
                });
            }),
        ];
    }
}
