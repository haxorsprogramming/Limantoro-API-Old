<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
  protected $primaryKey = 'number';
  protected $keyType = 'string';
    protected $fillable = [
        'number', 'date','purchase_order_number',
        'admin_code','supplier_code'
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
    // public function getFullNameAttribute()
    // {
    //     return "{$this->first_name} {$this->last_name}";
    // }
    // public function getCodeAttribute()
    // {
    //     return "PR-".\Str::padLeft($this->id, 10,"0");
    // }

    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = strtoupper($value);
    }

    public function supplier()
    {
      return $this->belongsTo(Supplier::class,'supplier_code','code');
    }

    public function purchase_order()
    {
      return $this->belongsTo(PurchaseOrder::class,'purchase_order_number','number');
    }

    public function purchase_return_details()
    {
      return $this->hasMany(PurchaseReturnDetail::class,'purchase_return_number','number');
    }

    public function admin()
    {
      return $this->belongsTo(User::class,'admin_code','code');
    }


}
