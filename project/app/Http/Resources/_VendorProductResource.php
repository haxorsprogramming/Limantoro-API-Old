<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\MyLib;


class VendorProductResource extends JsonResource
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

        $result =[
          'id' => $this->id,
          'product' => new ProductResource($this->product),
          'vendor' => new VendorResource($this->vendor),
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at,
        ];

        $admin = MyLib::admin();
        if (!in_array($admin->role->title,["Manager Lapangan"])) {
          $result['last_price']= $this->last_price;
        }

        return $result;

    }
}
