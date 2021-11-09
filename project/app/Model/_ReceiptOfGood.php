<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReceiptOfGood extends Model
{

    protected $fillable = [
        'code',
        'vendor_id',
        'receipt_date',
        'admin_id',
        'project_id',
        'invoice_id',
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

    public function vendor()
    {
      return $this->belongsTo(Vendor::class,'vendor_id','id');
    }

    public function project()
    {
      return $this->belongsTo(Project::class,'project_id','id');
    }


}
