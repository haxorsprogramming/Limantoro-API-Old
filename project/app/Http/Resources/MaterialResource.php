<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
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
            'satuan' => $this->satuan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
