<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            'purchase_request'=>new PurchaseRequestResource($this->whenLoaded('purchase_request')),
            'supplier'=>new SupplierResource($this->whenLoaded('supplier')),
            'proof_of_payment_number' => $this->proof_of_payment_number,
            'approver'=>new UserResource($this->whenLoaded('approver')),
            'proof_of_expenditure_number'=>$this->proof_of_expenditure_number,
            'purchase_order_details'=>PurchaseOrderDetailResource::collection($this->whenLoaded('purchase_order_details')),
            'locker'=>new UserResource($this->whenLoaded('locker')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
