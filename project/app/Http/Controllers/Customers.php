<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\Customer;

use App\Http\Requests\CustomerReq;
use App\Http\Resources\CustomerResource;

use App\Helpers\MyLib;

class Customers extends Controller
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
      $customers = Customer::offset($offset)->limit($limit);

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
          $customers = $customers->orderBy("name",$sortList["name"]);
        }

        if (isset($sortList["address"])) {
          $customers = $customers->orderBy("address",$sortList["address"]);
        }

        if (isset($sortList["city"])) {
          $customers = $customers->orderBy("city",$sortList["city"]);
        }

        if (isset($sortList["contact_person"])) {
          $customers = $customers->orderBy("contact_person",$sortList["contact_person"]);
        }

        if (isset($sortList["phone_number"])) {
          $customers = $customers->orderBy("phone_number",$sortList["phone_number"]);
        }

        if (isset($sortList["npwp"])) {
          $customers = $customers->orderBy("npwp",$sortList["npwp"]);
        }

        if (isset($sortList["created_at"])) {
          $customers = $customers->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $customers = $customers->orderBy("updated_at",$sortList["updated_at"]);
        }
        //
        // if (isset($sortList["role"])) {
        //   $customers = $customers->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $customers = $customers->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $customers = $customers->orderBy('code','ASC');
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
          $customers = $customers->where("code","like",$likeList["code"]);
        }

        if (isset($likeList["name"])) {
          $customers = $customers->orWhere("name","like",$likeList["name"]);
        }

        if (isset($likeList["address"])) {
          $customers = $customers->where("address","like",$likeList["address"]);
        }

        if (isset($likeList["city"])) {
          $customers = $customers->orWhere("city","like",$likeList["city"]);
        }
        if (isset($likeList["contact_person"])) {
          $customers = $customers->where("contact_person","like",$likeList["contact_person"]);
        }

        if (isset($likeList["phone_number"])) {
          $customers = $customers->orWhere("phone_number","like",$likeList["phone_number"]);
        }

        if (isset($likeList["npwp"])) {
          $customers = $customers->orWhere("npwp","like",$likeList["npwp"]);
        }

      }

      // ==============
      // Model Filter
      // ==============
      // if (isset($request->code)) {
      //   $customers = $customers->where("code",'like','%'.$request->code.'%');
      // }
      // if (isset($request->name)) {
      //   $customers = $customers->where("name",'like','%'.$request->name.'%');
      // }
      // if (isset($request->address)) {
      //   $customers = $customers->where("address",'like','%'.$request->address.'%');
      // }
      // if (isset($request->city)) {
      //   $customers = $customers->where("city",'like','%'.$request->city.'%');
      // }
      // if (isset($request->contact_person)) {
      //   $customers = $customers->where("contact_person",'like','%'.$request->contact_person.'%');
      // }
      // if (isset($request->phone_number)) {
      //   $customers = $customers->where("phone_number",'like','%'.$request->phone_number.'%');
      // }
      // if (isset($request->npwp)) {
      //   $customers = $customers->where("npwp",'like','%'.$request->npwp.'%');
      // }

      $customers=$customers->get();

      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        "data"=>CustomerResource::collection($customers),
      ],200);
    }

    public function store(CustomerReq $request)
    {
      $data=new Customer($request->except(['_token']));
      $data->admin_code=$this->admin->code;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new CustomerResource($data)
        ],200);
      }
    }

    public function update(CustomerReq $request)
    {
      $code = $request->code;
      $new_code = $request->new_code;

      $data = Customer::where("code",$code)->first();
      $data->admin_code=$this->admin->code;
      $data->name=$request->name ?? $data->name;
      $data->address=$request->address ?? $data->address;
      $data->city=$request->city ?? $data->city;
      $data->contact_person=$request->contact_person ?? $data->contact_person;
      $data->phone_number=$request->phone_number ?? $data->phone_number;
      $data->npwp=$request->npwp ?? $data->npwp;
      if ($new_code && $new_code!=$code) {
        $data->code=$new_code;
      }

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new CustomerResource($data)
        ],200);
      }
    }

    public function show(Request $request)
    {
      $rules = [
         'code' => 'required|min:3|exists:\App\Model\Customer,code',
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

      $data = Customer::where("code",$request->code)->first();

      return response()->json([
        "data"=>new CustomerResource($data),
      ],200);
    }
}
