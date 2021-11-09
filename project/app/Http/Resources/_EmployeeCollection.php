<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
// use UserResource;
// use App\Http\Resources\UserResource;
class EmployeeCollection extends ResourceCollection
{
  protected $withoutFields = [];

  public function toArray($request)
  {
    return $this->processCollection($request);
  }

  public function hide(array $fields)
  {
    $this->withoutFields = $fields;
    return $this;
  }

  protected function processCollection($request)
  {
    // return $this->collection->map(function (UserResource $resource) use ($request,$withoutFields) {
    //   return $resource->hide($this->withoutFields)->parent::toArray($request);
    // })->all();
    return $this->collection->map(function ($resource) use ($request) {
      return EmployeeResource::make($resource)->hide($this->withoutFields);
    })->all();
  }

}
