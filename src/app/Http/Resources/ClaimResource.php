<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'claimed_amount' => $this->claimed_amount,
            'formatted_claimed_amount' => $this->formatted_claimed_amount,
            'claim_date' => $this->claim_date ? $this->claim_date->format('Y-m-d') : null,
            'resolution_date' => $this->resolution_date ? $this->resolution_date->format('Y-m-d') : null,
            'resolution_notes' => $this->resolution_notes,
            'attachments' => $this->attachments,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
            
            // Связанные данные
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'title' => $this->product->title,
                    'model' => $this->product->model,
                    'serial_number' => $this->product->serial_number,
                    'price' => $this->product->price,
                    'formatted_price' => $this->product->formatted_price,
                ];
            }),
            
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
