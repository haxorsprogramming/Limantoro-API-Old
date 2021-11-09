<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
  protected $fillable = [
    'id','code','title','side','balance_sheet_group'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  // protected $hidden = [
  //     'password', 'api_token',
  // ];

  // public function seller()
  // {
  //   return $this->belongsTo(Seller::Class);
  // }

}
