<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\JournalDetailResource;
class JournalResource extends JsonResource
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
            'journal_details'=>JournalDetailResource::collection($this->journal_details),
            // 'code' => $this->code,
            // 'title' => $this->title,
            // 'side' => $this->side,
            // 'balance_sheet_group' => $this->balance_sheet_group,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
