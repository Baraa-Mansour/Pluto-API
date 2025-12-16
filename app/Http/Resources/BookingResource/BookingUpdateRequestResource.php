<?php

namespace App\Http\Resources\BookingResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BookingResource\BookingsForUserResource;

class BookingUpdateRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'booking' => new BookingsForUserResource($this->booking),
            'new_from_date' => $this->new_from_date->format('Y-m-d'),
            'new_to_date' => $this->new_to_date->format('Y-m-d')
        ];
    }
}
