<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ChartOfAccountResource;
class JournalDetailResource extends JsonResource
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
            'journal' =>[
              'id'=>$this->journal->id,
            ],
            'chart_of_account' =>new ChartOfAccountResource($this->chart_of_account),

            // 'chart_of_account' =>[
            //   'balance_sheet_group'=>$this->chart_of_account->balance_sheet_group,
            //   'code'=>$this->chart_of_account->code,
            //   'created_at'=>$this->chart_of_account->created_at,
            //   'id'=>$this->chart_of_account->id,
            //   'side'=>$this->chart_of_account->side,
            //   'title'=>$this->chart_of_account->title,
            //   'updated_at'=>$this->chart_of_account->updated_at,
            // ],
            'debit' => $this->debit,
            'credit' => $this->credit,
            'description' => $this->description ?? '',
            'ref' => $this->ref ?? '',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];;

    }
}
