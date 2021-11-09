<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProofOfExpenditureDetailResource extends JsonResource
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
            'proof_of_expenditure' => new ProofOfExpenditureResource($this->whenLoaded('proof_of_expenditure')),
            'description'=>$this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
