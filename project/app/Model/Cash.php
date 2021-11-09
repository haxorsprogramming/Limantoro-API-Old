<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
  protected $primaryKey = 'code';
  protected $keyType = 'string';

    protected $fillable = [
        'code','name', 'no_acc','admin_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'id',
    // ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    // protected $visible = [
    //     'id','title'
    // ];

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

}
