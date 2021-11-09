<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'contact_person' => $this->contact_person,
            'phone_number' => $this->phone_number,
            'npwp' => $this->npwp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
