<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
  protected $primaryKey = 'code';
  protected $keyType = 'string';

  protected $fillable = [
    'admin_code','user_code','code','id_number','name','photo',
    'birth_date','address','gender','position','type'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  // protected $hidden = [
  //     'password', 'api_token',
  // ];

  public function user()
  {
    return $this->belongsTo(User::Class,'user_code','code');
  }

}
