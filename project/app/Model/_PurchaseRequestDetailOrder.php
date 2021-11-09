<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestDetailOrder extends Model
{

    protected $fillable = [
        'purchase_request_detail_id',
        'vendor_id',
        'product_id',
        'qty',
        'price',
        'admin_id'
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

    // public function request()
    // {
    //   return $this->belongsTo(User::class,'request_by','id');
    // }
    //
    // public function getCodeAttribute()
    // {
    //     return "PO-".\Str::padLeft($this->id, 10,"0");
    // }

    public function purchase_request_detail()
    {
      return $this->belongsTo(PurchaseRequestDetail::class,'purchase_request_detail_id','id');
    }
    public function vendor()
    {
      return $this->belongsTo(Vendor::class,'vendor_id','id');
    }




}
