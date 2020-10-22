<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnimalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type_id' => isset($this->type) ? $this->type->id : null,
            'type_name' => isset($this->type) ? $this->type->name : null,
            'name' => $this->name,
            'birthday' => $this->birthday,
            'age' => $this->age,  // 說明2
            'area' => $this->area,
            'fix' => $this->fix,
            'description' => $this->description,
            'personality' => $this->personality,
            'created_at' => (string)$this->created_at, // 說明3
            'updated_at' => (string)$this->updated_at,
            'user_id' => $this->user_id,
        ];
    }
}
