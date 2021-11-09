<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    // protected $fillable = [
    //     'username', 'password','image','admin_id'
    // ];

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

    protected $visible = [
        'id','title'
    ];


}
