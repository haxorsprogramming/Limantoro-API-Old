<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Str;

class User extends Authenticatable
{
    use Notifiable;
    protected $primaryKey = 'code';
    protected $keyType = 'string';

    protected $fillable = [
        'code','admin_code','id_number','name','photo',
        'birth_date','address','gender','role_id','type',
        'password','is_active','can_login',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'username', 'password','image','admin_id'
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $visible = [
        'code', 'created_at','updated_at',
        'image','is_active'
    ];

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function generateToken()
    {
      $this->api_token=Str::random(255);
      $this->save();
      return $this->api_token;
    }

    public function role()
    {
      return $this->hasOne(Role::class,"id","role_id");
    }

    public function employee()
    {
      return $this->hasOne(Employee::class,"user_code","code");
    }


}
