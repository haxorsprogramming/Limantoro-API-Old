<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  // public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    //
    //  public static function collection($resource)
    // {
    //   return tap(new UserCollection($resource), function ($collection) {
    //   $collection->collects = __CLASS__;
    //   });
    // }

     // protected $withoutFields = [];
     public function toArray($request)
    {

        // return parent::toArray($request);
        return [
            'code' => $this->code,
            'role' => new RoleResource($this->role),
            'is_active' => $this->is_active,
            'can_login' => $this->can_login,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'id_number' => $this->id_number??"",
            'name' => $this->name,
            'birth_date' => $this->birth_date??date("Y-m-d"),
            'address' => $this->address??"",
            'gender' => $this->gender??"",
            'type' => $this->type,
            'photo' => $this->photo ? config("app.url").$this->photo : "",
        ];
        // return $this->filterFields([
        //     'code' => $this->code,
        //     'image' => $this->image,
        //     'role' => new RoleResource($this->role),
        //     'employee' => new EmployeeResource($this->whenLoaded('employee')),
        //     'is_active' => $this->is_active,
        //     'created_at' => $this->created_at,
        //     'updated_at' => $this->updated_at,
        // ]) ;
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
