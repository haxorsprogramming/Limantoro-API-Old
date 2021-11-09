<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReturnResource extends JsonResource
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
            'date' => $this->date,
            'purchase_order'=>new PurchaseOrderResource($this->whenLoaded('purchase_order')),
            'supplier'=>new SupplierResource($this->whenLoaded('supplier')),
            // 'checker'=>new UserResource($this->whenLoaded('checker')),
            // 'delivery_order_letter_number' => $this->delivery_order_letter_number,
            'goods_return_details'=>GoodsReturnDetailResource::collection($this->whenLoaded('goods_return_details')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
