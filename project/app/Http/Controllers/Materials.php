<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\Material;

use App\Http\Requests\MaterialReq;
use App\Http\Resources\MaterialResource;

use App\Helpers\MyLib;
use PDF;
class Materials extends Controller
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

      $materials = Material::offset($offset)->limit($limit);

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

        if (isset($sortList["code"])) {
          $materials = $materials->orderBy("code",$sortList["code"]);
        }
        if (isset($sortList["name"])) {
          $materials = $materials->orderBy("name",$sortList["name"]);
        }
        if (isset($sortList["satuan"])) {
          $materials = $materials->orderBy("satuan",$sortList["satuan"]);
        }

        if (isset($sortList["created_at"])) {
          $materials = $materials->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $materials = $materials->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $materials = $materials->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $materials = $materials->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $materials = $materials->orderBy('code','ASC');
      }

      // ==============
      // Model Filter
      // ==============
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
          $materials = $materials->where("code","like",$likeList["code"]);
        }

        if (isset($likeList["name"])) {
          $materials = $materials->orWhere("name","like",$likeList["name"]);
        }


      }
      // if (isset($request->code)) {
      //   $materials = $materials->where("code",'like','%'.$request->code.'%');
      // }
      //
      // if (isset($request->name)) {
      //   $materials = $materials->where("name",'like','%'.$request->name.'%');
      // }
      //
      // if (isset($request->admin_code)) {
      //   $materials = $materials->where("admin_code",'like','%'.$request->admin_code.'%');
      // }

      $materials=$materials->get();

      return response()->json([
        "data"=>MaterialResource::collection($materials),
      ],200);
    }

    public function store(MaterialReq $request)
    {
      $data=new Material($request->except(['_token']));

      $data->admin_code=$this->admin->code;
      // $data->code=strtoupper($request->code);

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new MaterialResource($data)
        ],200);
      }
    }

    public function update(MaterialReq $request)
    {
      $code = $request->code;
      $new_code = $request->new_code;
      // if ($code=="") {
      //   throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      // }

      $data = Material::where("code",$request->code)->first();
      $data->name=$request->name;
      $data->satuan=$request->satuan;
      $data->admin_code=$this->admin->code;
      if ($new_code && $new_code!=$code) {
        $data->code=$new_code;
      }
      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new MaterialResource($data),
          "code"=>$request->code,
          "new_code"=>$request->new_code,
        ],200);
      }
    }

    public function show(Request $request)
    {
      $data = Material::where("code",$request->code)->first();

      return response()->json([
        "data"=>new MaterialResource($data),
      ],200);
    }

    public function delete(Request $request)
    {

      $rules = [
         'code' => 'required|min:3|exists:\App\Model\Material,code',
      ];

      $messages=[
         'code.required' => 'Kode tidak boleh kosong',
         'code.min' => 'Kode minimal 3 karakter',
         'code.exists' => 'Kode tidak terdaftar',
      ];


      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $code = $request->code;
      // if ($code=="") {
      //   throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      // }

      // check apakah sudah digunakan di table lain

      // $vendor_material = \App\Model\VendorMaterial::where("material_code",$code)->first();
      // $purchase_request_detail = \App\Model\PurchaseRequestDetail::where("material_code",$code)->first();

      // if ($vendor_material || $purchase_request_detail) {
      //   throw new MyException("Data produk sudah digunakan sehingga tidak bisa dihapus");
      // }
      $data = Material::where("code",$code)->delete();

      return response()->json([
        "message"=>"delete complete",
      ],200);
    }




    public function getDataByVendor(Request $request)
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


      $materials = Material::select('materials.*')->join('vendor_materials',function($join)use($request){
        $join->select('material_code')
        ->on('vendor_materials.material_code','=','materials.code')
        ->where('vendor_id',$request->vendor_id);
      });

      $materials=$materials->offset($offset)->limit($limit);

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

        if (isset($sortList["code"])) {
          $materials = $materials->orderBy("code",$sortList["code"]);
        }
        if (isset($sortList["name"])) {
          $materials = $materials->orderBy("name",$sortList["name"]);
        }
        if (isset($sortList["satuan"])) {
          $materials = $materials->orderBy("satuan",$sortList["satuan"]);
        }

        if (isset($sortList["created_at"])) {
          $materials = $materials->orderBy("materials.created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $materials = $materials->orderBy("materials.updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $materials = $materials->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $materials = $materials->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $materials = $materials->orderBy('code','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      if (isset($request->code)) {
        $materials = $materials->where("code",'like','%'.$request->code.'%');
      }

      if (isset($request->name)) {
        $materials = $materials->where("name",'like','%'.$request->name.'%');
      }

      // if (isset($request->admin_id)) {
      //   $materials = $materials->where("admin_id",'like','%'.$request->admin_id.'%');
      // }

      $materials=$materials->get();

      return response()->json([
        "data"=>MaterialResource::collection($materials),
      ],200);
    }
}
