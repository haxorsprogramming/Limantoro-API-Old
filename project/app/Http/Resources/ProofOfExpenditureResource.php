<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProofOfExpenditureResource extends JsonResource
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
            'is_paid' => $this->is_paid,
            'pay_date' => $this->pay_date,
            'note' => $this->note ?? "",
            'bank_1' => $this->bank_1 ?? "",
            'check_number_1' => $this->check_number_1 ?? "",
            'total_1' => $this->total_1 ?? "",
            'bank_2' => $this->bank_2 ?? "",
            'check_number_2' => $this->check_number_2 ?? "",
            'total_2' => $this->total_2 ?? "",
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'proof_of_expenditure_details'=>ProofOfExpenditureDetailResource::collection($this->whenLoaded('proof_of_expenditure_details')),
            'admin'=>new UserResource($this->whenLoaded('admin')),
            'discount' => $this->discount ?? 0,
        ];;

    }
}
