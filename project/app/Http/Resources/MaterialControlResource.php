<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaterialControlResource extends JsonResource
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
            'project' => new ProjectResource($this->whenLoaded('project')),
            'ordinal' => $this->ordinal,
            'material'=>new MaterialResource($this->whenLoaded('material')),
            'qty'=>$this->qty,
            'status'=>$this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
