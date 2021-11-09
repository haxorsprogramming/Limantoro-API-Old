<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetail extends Model
{

    protected $fillable = [
        'ordinal', 'material_code',
        'note','purchase_request_number',
        'admin_code','requested_qty','approved_qty'
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
    public function setPurchaseRequestNumberAttribute($value)
    {
        $this->attributes['purchase_request_number'] = strtoupper($value);
    }

    public function setMaterialCodeAttribute($value)
    {
        $this->attributes['material_code'] = strtoupper($value);
    }

    public function setAdminCodeAttribute($value)
    {
        $this->attributes['admin_code'] = strtoupper($value);
    }

    public function purchase_request()
    {
      return $this->belongsTo(PurchaseRequest::class,'purchase_request_id','id');
    }

    public function product()
    {
      return $this->belongsTo(Product::class,'product_id','id');
    }

    public function purchase_request_detail_orders()
    {
      return $this->hasMany(PurchaseRequestDetailOrder::class,'purchase_request_detail_id','id');
    }

    public function material()
    {
      return $this->belongsTo(Material::class,'material_code','code');
    }
}
