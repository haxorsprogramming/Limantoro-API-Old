<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VendorProduct extends Model
{

    protected $fillable = [
        'product_id', 'price','vendor_id','admin_id'
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

    public function product()
    {
      return $this->hasOne(Product::class,'id','product_id');
    }

    public function vendor()
    {
      return $this->hasOne(Vendor::class,'id','vendor_id');
    }

}
