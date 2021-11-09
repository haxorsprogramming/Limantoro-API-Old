<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Str;
use Hash;
// use App\Jobs\SendVerificationEmail;
use Illuminate\Support\Facades\Validator;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\Role;
use App\Helpers\MyLib;
use Illuminate\Validation\Rule;

class Roles extends Controller
{

  public function __construct()
  {
      $this->admin = MyLib::admin();
  }

  public function index(Request $request)
  {

    //======================================================================================================
    // Pembatasan Data hanya memerlukan limit dan offset
    //======================================================================================================
    $limit = 250; // Limit +> Much Data
    if (isset($request->limit)) {
      if ($request->limit <= 250) {
        $limit = $request->limit;
      }else {
        throw new MyException("Max Limit 250");
      }
    }

    $offset = isset($request->offset) ? (int) $request->offset : 0; // example offset 400 start from 401

    //======================================================================================================
    // Jika Halaman Ditentutkan maka $offset akan disesuaikan
    //======================================================================================================
    if (isset($request->page)) {
      $page =  (int) $request->page;
      $offset = ($page*$limit)-$limit;
    }

    //======================================================================================================
    // Init Model
    //======================================================================================================
    $roles = Role::offset($offset)->limit($limit);

    //---- All Developer Hidden
    $roles=$roles->where("id","!=",0);

    //======================================================================================================
    // Model Sorting | Example $request->sort = "username:desc,role:desc";
    //======================================================================================================

    if ($request->sort) {
      $sortList=[];

      $sorts=explode(",",$request->sort);
      foreach ($sorts as $key => $sort) {
        $side = explode(":",$sort);
        $side[1]=isset($side[1])?$side[1]:'ASC';
        $sortList[$side[0]]=$side[1];
      }

      if (isset($sortList["title"])) {
        $roles = $roles->orderBy("title",$sortList["title"]);
      }

      if (isset($sortList["created_at"])) {
        $roles = $roles->orderBy("created_at",$sortList["created_at"]);
      }

      if (isset($sortList["updated_at"])) {
        $roles = $roles->orderBy("updated_at",$sortList["updated_at"]);
      }

      // if (isset($sortList["role"])) {
      //   $roles = $roles->orderBy(function($q){
      //     $q->from("roles")
      //     ->select("title")
      //     ->whereColumn("id","users.role_id");
      //   },$sortList["role"]);
      // }

      if (isset($sortList["admin"])) {
        $roles = $roles->orderBy(function($q){
          $q->from("users as u")
          ->select("u.username")
          ->whereColumn("u.id","users.id");
        },$sortList["admin"]);
      }
    }else {
      $roles = $roles->orderBy('title','ASC');
    }

    // ==============
    // Model Filter
    // ==============

    if (isset($request->title)) {
      $roles = $roles->where("title",'like','%'.$request->title.'%');
    }

    $roles=$roles->get();
    return response()->json([
      "data"=>$roles,
    ],200);
  }

}
