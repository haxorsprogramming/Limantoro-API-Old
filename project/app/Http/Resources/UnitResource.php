<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
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
            'ordinal' => $this->ordinal,
            'name' => $this->name,
            'land_size' => $this->land_size,
            'building_size' => $this->building_size,
            'builded' => $this->builded,
            'sold' => $this->sold,
            'selling_price' => $this->selling_price,
            'marketing_fee' => $this->marketing_fee,
            'project'=>new ProjectResource($this->whenLoaded('project')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
