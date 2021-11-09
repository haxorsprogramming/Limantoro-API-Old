<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MaterialControl extends Model
{

    protected $fillable = [
        'ordinal', 'material_code',
        'project_code','qty',
        'admin_code','status'
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
    public function setProjectCodeAttribute($value)
    {
        $this->attributes['project_code'] = strtoupper($value);
    }

    public function setMaterialCodeAttribute($value)
    {
        $this->attributes['material_code'] = strtoupper($value);
    }

    public function setAdminCodeAttribute($value)
    {
      $this->attributes['admin_code'] = strtoupper($value);
    }

    public function project()
    {
      return $this->belongsTo(Project::class,'project_code','code');
    }

    public function material()
    {
      return $this->belongsTo(Material::class,'material_code','code');
    }
}
