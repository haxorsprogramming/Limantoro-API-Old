<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptOfGoodResource extends JsonResource
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
            'code' => $this->code,
            'vendor'=>new VendorResource($this->vendor),
            'project'=>new ProjectResource($this->project),
            'receipt_date' => $this->receipt_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
