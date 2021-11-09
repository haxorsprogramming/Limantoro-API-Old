<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
  protected $primaryKey = 'number';
  protected $keyType = 'string';
    protected $fillable = [
        'number', 'date',
        'requester_code',
        'admin_code','project_code'
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
    public function requester()
    {
      return $this->belongsTo(User::class,'requester_code','code');
    }

    public function project()
    {
      return $this->belongsTo(Project::class,'project_code','code');
    }

    public function purchase_request_details()
    {
      return $this->hasMany(PurchaseRequestDetail::class,'purchase_request_number','number');
    }

    public function approver()
    {
      return $this->belongsTo(User::class,"approver_code","code");
    }

}
