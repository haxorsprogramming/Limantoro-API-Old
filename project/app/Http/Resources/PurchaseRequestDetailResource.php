<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestDetailResource extends JsonResource
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
            'note' => $this->note??"",
            'purchase_request' => new PurchaseRequestResource($this->whenLoaded('purchase_request')),
            'requested_qty'=>$this->requested_qty,
            'approved_qty'=>$this->approved_qty,
            // 'list_products'=>PurchaseOrderDetailResource::collection($this->list_products),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
