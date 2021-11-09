<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
  // protected $primaryKey = ['ordinal', 'project_code'];
  // protected $keyType = 'string';
    protected $fillable = [
        'project_code'
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

    // public function project()
    // {
    //   return $this->hasOne(Project::class,'id','project_id');
    // }


    public function setProjectCodeAttribute($value)
    {
        $this->attributes['project_code'] = strtoupper($value);
    }

    public function setAdminCodeAttribute($value)
    {
        $this->attributes['admin_code'] = strtoupper($value);
    }


}
