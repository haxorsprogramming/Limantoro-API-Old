<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Excel;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\Unit;

use App\Http\Requests\UnitStore;
use App\Http\Requests\UnitReq;
use App\Http\Resources\UnitResource;
use App\Exports\EmployeeReport;

use App\Helpers\MyLib;
use Image;
use File;
use Illuminate\Validation\Rule;

class Units extends Controller
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
      $units = Unit::offset($offset)->limit($limit);

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
          $units = $units->orderBy("name",$sortList["name"]);
        }

        if (isset($sortList["created_at"])) {
          $units = $units->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $units = $units->orderBy("updated_at",$sortList["updated_at"]);
        }

        if (isset($sortList["project_id"])) {
          $units = $units->orderBy(function($q){
            $q->from("projects")
            ->select("id")
            ->whereColumn("id","units.project_id");
          },$sortList["role"]);
        }

        if (isset($sortList["admin"])) {
          $units = $units->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $units = $units->orderBy('id','ASC');
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

        if (isset($likeList["project_code"])) {
          $employees = $employees->where("project_code","like",$likeList["project_code"]);
        }

        if (isset($likeList["name"])) {
          $employees = $employees->orWhere("name","like",$likeList["name"]);
        }


      }
      // // ==============
      // // Model Filter
      // // ==============
      //
      // if (isset($request->name)) {
      //   $units = $units->where("name",'like','%'.$request->name.'%');
      // }
      // if (isset($request->product_name)) {
      //   $vendor_products = $units->whereIn('product_id',function($q)use($request){
      //     $q->select('id');
      //     $q->from('products');
      //     $q->where('code','like','%'.$request->product_name.'%');
      //   });
      // }
      // if (isset($request->admin_id)) {
      //   $units = $units->where("admin_id",'like','%'.$request->admin_id.'%');
      // }

      // // Words => Kata/Kalimat yang akan dicari
      // $req_words = $request->words;
      // if ($req_words) {
      //   $datas = $datas->where('name','like','%'.$req_words.'%')
      //   ->orWhere('land_size','like','%'.$req_words.'%')
      //   ->orWhere('building_size','like','%'.$req_words.'%')
      //   ->orWhere('selling_price','like','%'.$req_words.'%')
      //   ->orWhere('marketing_fee','like','%'.$req_words.'%');
      // }
      $units=$units->get();

      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        // "data"=>$employees,
        "data"=>UnitResource::collection($units),
      ],200);
    }

    public function store(UnitReq $request)
    {
      $data=new Unit($request->except(['id','_token']));
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new UnitResource($data)
        ],200);
      }
    }

    public function update(UnitReq $request)
    {
      $id = $request->id;
      if ($id=="") {
        throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      }

      $data = Unit::where("id",$id)->first();
      $data->admin_id=$this->admin->id;
      $data->name=$request->name ?? $data->name;
      $data->land_size=$request->land_size ?? $data->land_size;
      $data->building_size=$request->building_size ?? $data->building_size;
      $data->builded=$request->builded ?? $data->builded;
      $data->sold=$request->sold ?? $data->sold;
      $data->selling_price=$request->selling_price ?? $data->selling_price;
      $data->marketing_fee=$request->marketing_fee ?? $data->marketing_fee;
      $data->project_id=$request->project_id ?? $data->project_id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new UnitResource($data)
        ],200);
      }
    }

    public function show($id,Request $request)
    {

      // $rules = [
      //    'id' => 'required|exists:App\Model\Unit,id',
      // ];
      //
      // $messages=[
      //    'id.required' => 'Nomor ID Unit Harus Ada',
      //    'id.exists' => 'Nomor ID Unit tidak terdaftar',
      // ];
      //
      // $validator = \Validator::make($request->all(),$rules,$messages);
      // if ($validator->fails()) {
      //   throw new MyException($validator->messages()->all());
      // }

      if (!isset($id)) {
        throw new MyException("Maaf Data input yang dimasukkan kurang lengkap");
      }

      if ($id==0) {
        return response()->json([
          "data"=>[],
        ],200);
      }

      $data = Unit::where("id",$id)->first();

      return response()->json([
        "data"=>new UnitResource($data),
      ],200);
    }
  // public function cetak(Request $request)
  // {
  //         $filename = $request->filename ?? "tx-".MyLib::timestamp();
  //         $employees = Employee::all();
  //
  //         // $mime=MyLib::mime("pdf");
  //         // $pdf = PDF::loadView('laporan.employee', ["data"=>$employees])->setPaper('a4', 'landscape');
  //         // $bs64=base64_encode($pdf->download($filename.'.pdf'));
  //
  //         $mime=MyLib::mime("xlsx");
  //         $bs64=base64_encode(Excel::raw(new EmployeeReport($employees), $mime["exportType"]));
  //
  //         $result =[
  //           "contentType"=>$mime["contentType"],
  //           "data"=>$bs64,
  //           "dataBase64"=>$mime["dataBase64"].$bs64,
  //           "filename"=>$filename
  //         ];
  //
  //         return $result;
  // }


}
