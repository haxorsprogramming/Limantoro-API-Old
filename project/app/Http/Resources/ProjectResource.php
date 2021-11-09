<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
  public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'date' => $this->date,
            'address' => $this->address,
            'is_finished' => $this->is_finished,
            'in_charge'=>new UserResource($this->whenLoaded('in_charge')),
            'units'=>UnitResource::collection($this->whenLoaded('units')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
