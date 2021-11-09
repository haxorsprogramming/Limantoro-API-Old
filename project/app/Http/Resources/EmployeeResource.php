<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
  public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    //  public static function collection($resource)
    // {
    //   return tap(new EmployeeCollection($resource), function ($collection) {
    //   $collection->collects = __CLASS__;
    //   });
    // }

    // protected $withoutFields = [];

    public function toArray($request)
    {

      return [
          'code' => $this->code,
          'id_number' => $this->id_number,
          'user' => new UserResource($this->whenLoaded('user')),
          'name' => $this->name,
          'birth_date' => $this->birth_date,
          'address' => $this->address,
          'gender' => $this->gender,
          'position' => $this->position,
          'type' => $this->type,
          'photo' => $this->photo ? config("app.url").$this->photo : "",
          'created_at' => $this->created_at,
          'updated_at' => $this->updated_at,
      ];

        // return parent::toArray($request);
        // return $this->filterFields([
        //     'code' => $this->code,
        //     'id_number' => $this->id_number,
        //     'user' => new UserResource($this->whenLoaded('user')),
        //     'name' => $this->name,
        //     'birth_date' => $this->birth_date,
        //     'address' => $this->address,
        //     'gender' => $this->gender,
        //     'position' => $this->position,
        //     'type' => $this->type,
        //     'photo' => $this->photo ? config("app.url").$this->photo : "",
        //     'created_at' => $this->created_at,
        //     'updated_at' => $this->updated_at,
        // ]);

    }

   //  public function hide(array $fields)
   // {
   //   $this->withoutFields = $fields;
   //   return $this;
   // }
   //
   // protected function filterFields($array)
   // {
   //   return collect($array)->forget($this->withoutFields)->toArray();
   // }
}
