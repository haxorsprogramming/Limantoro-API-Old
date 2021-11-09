<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
  protected $primaryKey = 'code';
  protected $keyType = 'string';

    protected $fillable = [
        'code','name','type','date', 'is_finished','in_charge_id'
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

    public function in_charge()
    {
      return $this->belongsTo(User::class,"in_charge_code","code");
    }

    public function units()
    {
      return $this->hasMany(Unit::class,"project_code","code");
    }

    public function purchase_requests()
    {
      return $this->hasMany(PurchaseRequest::class,'project_code','code');
    }
}
