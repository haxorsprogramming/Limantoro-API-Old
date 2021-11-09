<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReceiptOfGoodDetail extends Model
{

    protected $fillable = [
        'receipt_of_good_id',
        'product_id',
        'qty',
        'admin_id',
        'created_at',
        'updated_at'
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
    //

    public function receipt_of_good()
    {
      return $this->belongsTo(ReceiptOfGood::class,'receipt_of_good_id','id');
    }

    public function product()
    {
      return $this->belongsTo(Product::class,'product_id','id');
    }

}
