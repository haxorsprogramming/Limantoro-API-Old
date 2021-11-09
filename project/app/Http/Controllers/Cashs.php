<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\Cash;

use App\Http\Requests\CashReq;
use App\Http\Resources\CashResource;

use App\Helpers\MyLib;

class Cashs extends Controller
{
    private $admin;

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
      $cashs = Cash::offset($offset)->limit($limit);

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

        if (isset($sortList["name"])) {
          $cashs = $cashs->orderBy("name",$sortList["name"]);
        }

        if (isset($sortList["address"])) {
          $cashs = $cashs->orderBy("address",$sortList["address"]);
        }

        if (isset($sortList["no_acc"])) {
          $cashs = $cashs->orderBy("no_acc",$sortList["no_acc"]);
        }

        if (isset($sortList["created_at"])) {
          $cashs = $cashs->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $cashs = $cashs->orderBy("updated_at",$sortList["updated_at"]);
        }
        //
        // if (isset($sortList["role"])) {
        //   $cashs = $cashs->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $cashs = $cashs->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $cashs = $cashs->orderBy('code','ASC');
      }
      //======================================================================================================
      // Model Filter | Example $request->like = "username:%username,role:%role%,name:role%,";
      //======================================================================================================

      if ($request->like) {
        $likeList=[];

        $likes=explode(",",$request->like);
        foreach ($likes as $key => $like) {
          $side = explode(":",$like);
          $side[1]=isset($side[1])?$side[1]:'';
          $likeList[$side[0]]=$side[1];
        }

        if (isset($likeList["code"])) {
          $cashs = $cashs->where("code","like",$likeList["code"]);
        }

        // if (isset($likeList["name"])) {
        //   $cashs = $cashs->orWhere("name","like",$likeList["name"]);
        // }


      }

      // ==============
      // Model Filter
      // ==============
      if (isset($request->code)) {
        $cashs = $cashs->where("code",'like','%'.$request->code.'%');
      }
      if (isset($request->name)) {
        $cashs = $cashs->where("name",'like','%'.$request->name.'%');
      }
      if (isset($request->no_acc)) {
        $cashs = $cashs->where("no_acc",'like','%'.$request->no_acc.'%');
      }

      $cashs=$cashs->get();

      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        "data"=>CashResource::collection($cashs),
      ],200);
    }

    public function store(CashReq $request)
    {
      $data=new Cash($request->except(['_token']));
      $data->admin_code=$this->admin->code;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new CashResource($data)
        ],200);
      }
    }

    public function update(CashReq $request)
    {
      $code = $request->code;
      $new_code = $request->new_code;

      $data = Cash::where("code",$code)->first();
      $data->admin_code=$this->admin->code;
      $data->name=$request->name ?? $data->name;
      $data->no_acc=$request->no_acc ?? $data->no_acc;
      if ($new_code && $new_code!=$code) {
        $data->code=$new_code;
      }
      
      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new CashResource($data)
        ],200);
      }
    }

    public function show(Request $request)
    {
      $rules = [
         'code' => 'required|min:3|exists:\App\Model\Cash,code',
      ];

      $messages=[
         'code.exists' => 'Kode tidak terdaftar',
         'code.required' => 'Kode tidak boleh kosong',
         'code.min' => 'Kode minimal 3 karakter',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $data = Cash::where("code",$request->code)->first();

      return response()->json([
        "data"=>new CashResource($data),
      ],200);
    }
}
