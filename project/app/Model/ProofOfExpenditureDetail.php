<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProofOfExpenditureDetail extends Model
{

    protected $fillable = [
        'ordinal', 'proof_of_expenditure_number',
        'description','admin_code'
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

    public function setProofOfExpenditureNumberAttribute($value)
    {
        $this->attributes['proof_of_expenditure_number'] = strtoupper($value);
    }

    public function setAdminCodeAttribute($value)
    {
        $this->attributes['admin_code'] = strtoupper($value);
    }

    public function proof_of_expenditure()
    {
      return $this->belongsTo(ProofOfExpenditure::class,'proof_of_expenditure_number','number');
    }


}
