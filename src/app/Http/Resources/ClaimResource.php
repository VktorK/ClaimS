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
            'status' => $this->status,
            'status_label' => $this->status_label,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'was_in_repair' => $this->was_in_repair,
            'service_center_documents' => $this->service_center_documents,
            'previous_defect' => $this->previous_defect,
            'current_defect' => $this->current_defect,
            'expertiseConducted' => $this->expertiseConducted,
            'expertiseData' => $this->expertiseData,
            'expertiseDefect' => $this->expertiseDefect,
            'actualDefect' => $this->actualDefect,
            'claimed_amount' => $this->claimed_amount,
            'formatted_claimed_amount' => $this->formatted_claimed_amount,
            'claim_date' => $this->claim_date ? $this->claim_date->format('Y-m-d') : null,
            'resolution_date' => $this->resolution_date ? $this->resolution_date->format('Y-m-d') : null,
            'resolution_notes' => $this->resolution_notes,
            'attachments' => $this->attachments,
            'product_id' => $this->product_id,
            'template_id' => $this->template_id,
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
            
            'template' => $this->whenLoaded('template', function () {
                return $this->template ? [
                    'id' => $this->template->id,
                    'name' => $this->template->name,
                    'description' => $this->template->description,
                ] : null;
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
