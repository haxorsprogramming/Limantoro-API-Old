<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
  protected $primaryKey = 'number';
  protected $keyType = 'string';
    protected $fillable = [
        'number', 'date','purchase_request_number',
        'approver_code','proof_of_payment_number',
        'admin_code','supplier_code','proof_of_expenditure_number'
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
    public function approver()
    {
      return $this->belongsTo(User::class,'approver_code','code');
    }

    public function supplier()
    {
      return $this->belongsTo(Supplier::class,'supplier_code','code');
    }

    public function purchase_request()
    {
      return $this->belongsTo(PurchaseRequest::class,'purchase_request_number','number');
    }

    public function purchase_order_details()
    {
      return $this->hasMany(PurchaseOrderDetail::class,'purchase_order_number','number');
    }

    public function admin()
    {
      return $this->belongsTo(User::class,'admin_code','code');
    }

    public function locker()
    {
      return $this->belongsTo(User::class,'lock_by','code');
    }

    public function purchase_returns()
    {
      return $this->hasMany(PurchaseReturn::class,'purchase_order_number','number');
    }
}
