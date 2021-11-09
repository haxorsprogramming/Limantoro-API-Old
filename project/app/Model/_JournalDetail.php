<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JournalDetail extends Model
{
  protected $fillable = [
    'id'
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

  public function journal()
  {
    return $this->belongsTo(Journal::class);
  }

  public function chart_of_account()
  {
    return $this->belongsTo(ChartOfAccount::class);
  }

}
