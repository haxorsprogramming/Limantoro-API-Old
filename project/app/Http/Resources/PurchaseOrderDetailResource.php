<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderDetailResource extends JsonResource
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
            'material'=>new MaterialResource($this->whenLoaded('material')),
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchase_order')),
            'qty'=>$this->qty,
            'price'=>$this->price,
            'note' => $this->note??"",
            // 'list_products'=>PurchaseOrderDetailResource::collection($this->list_products),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
