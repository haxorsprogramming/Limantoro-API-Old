<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReceiptDetailResource extends JsonResource
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
            'goods_receipt' => new GoodsReceiptResource($this->whenLoaded('goods_receipt')),
            'ordinal'=>$this->ordinal,
            'material'=>new MaterialResource($this->whenLoaded('material')),
            'qty' => $this->qty,
            'goods_receipt_details'=>GoodsReceiptDetailResource::collection($this->whenLoaded('goods_receipt_details')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
