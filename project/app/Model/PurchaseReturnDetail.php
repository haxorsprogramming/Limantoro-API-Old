<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{

    protected $fillable = [
        'ordinal', 'material_code',
        'purchase_return_number',
        'admin_code','qty'
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

    // public function purchase_request()
    // {
    //   return $this->belongsTo(PurchaseRequest::class,'purchase_request_id','id');
    // }

    public function setPurchaseReturnNumberAttribute($value)
    {
        $this->attributes['purchase_return_number'] = strtoupper($value);
    }

    public function setAdminCodeAttribute($value)
    {
        $this->attributes['admin_code'] = strtoupper($value);
    }

    public function setMaterialCodeAttribute($value)
    {
        $this->attributes['material_code'] = strtoupper($value);
    }

    public function purchase_return()    {
      return $this->belongsTo(PurchaseReturn::class,'purchase_return_number','number');
    }

    public function material()
    {
      return $this->belongsTo(Material::class,'material_code','code');
    }
}
