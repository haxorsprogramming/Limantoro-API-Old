<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReturnDetailResource extends JsonResource
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
            'purchase_return' => new PurchaseReturnResource($this->whenLoaded('purchase_return')),
            'ordinal'=>$this->ordinal,
            'material'=>new MaterialResource($this->whenLoaded('material')),
            'qty' => $this->qty,
            'purchase_return_details'=>PurchaseReturnDetailResource::collection($this->whenLoaded('purchase_return_details')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
