<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestDetailOrderResource extends JsonResource
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
            'id' => $this->id,
            'purchase_request_detail'=> new PurchaseRequestDetailResource($this->purchase_request_detail),
            'vendor'=> new VendorResource($this->vendor),
            'product'=> new ProductResource($this->product),
            'qty'=>$this->qty,
            'price'=>$this->price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
