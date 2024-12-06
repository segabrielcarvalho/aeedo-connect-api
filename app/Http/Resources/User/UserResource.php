<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Organ\OrganResource;
use App\Http\Resources\Patient\PatientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
            'role' => $this->role,
            'birthDate' => $this->birth_date,
            'isActive' => $this->status,
            'createdAt' => $this->created_at,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'organs' => $this->when(
                $this->relationLoaded('patient') && $this->patient->organs->isNotEmpty(),
                fn () => OrganResource::collection($this->patient->organs)
            ),
        ];
    }
}
