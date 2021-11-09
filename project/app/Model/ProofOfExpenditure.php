<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProofOfExpenditure extends Model
{
  protected $primaryKey = 'number';
  protected $keyType = 'string';
    protected $fillable = [
        'number', 'date','is_paid',
        'pay_date','note','admin_code',
        'bank_1','check_number_1','total_1',
        'bank_2','check_number_2','total_2',
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
    public function proof_of_expenditure_details()
    {
      return $this->hasMany(ProofOfExpenditureDetail::class,'proof_of_expenditure_number','number');
    }
    public function admin()
    {
      return $this->belongsTo(User::class,'admin_code','code');
    }


}
