<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
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

  public function journal_details()
  {
    return $this->hasMany(JournalDetail::Class);
  }

}
