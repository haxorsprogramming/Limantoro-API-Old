<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReturnDetailResource extends JsonResource
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
            'number' => $this->number,
            'goods_return' => new GoodsReturnResource($this->whenLoaded('goods_return')),
            'ordinal'=>$this->ordinal,
            'material'=>new MaterialResource($this->whenLoaded('material')),
            'qty' => $this->qty,
            'goods_return_details'=>GoodsReturnDetailResource::collection($this->whenLoaded('goods_return_details')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
