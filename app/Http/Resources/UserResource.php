<?php

namespace App\Http\Resources;

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
        return [
            'token' => $this['token'],
            'data' => [
                'name' => $this['user']->name,
                'email' => $this['user']->name,
                'role' => 'user',
                'email_verified_at' => 'null',
                'created_at' => $this['user']->created_at,
                'updated_at' => $this['user']->updated_at,
            ],
            'token_type' => 'Bearer'
        ];
    }
}
