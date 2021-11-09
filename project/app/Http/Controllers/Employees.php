<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
// use Str;
// use Hash;
use PDF;
use Excel;
// use App\Jobs\SendVerificationEmail;
use Illuminate\Support\Facades\Validator;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\User;
use App\Model\Employee;

use App\Http\Requests\EmployeeReq;

use App\Helpers\MyLib;
use App\Http\Resources\EmployeeResource;
use Image;
use File;
use App\Exports\EmployeeReport;
use Storage;

class Employees extends Controller
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
      $employees = Employee::offset($offset)->limit($limit);

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
          $employees = $employees->orderBy("code",$sortList["code"]);
        }

        if (isset($sortList["name"])) {
          $employees = $employees->orderBy("name",$sortList["name"]);
        }

        if (isset($sortList["created_at"])) {
          $employees = $employees->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $employees = $employees->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $employees = $employees->orderBy('code','ASC');
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
          $employees = $employees->where("code","like",$likeList["code"]);
        }

        if (isset($likeList["name"])) {
          $employees = $employees->orWhere("name","like",$likeList["name"]);
        }


      }

      // ==============
      // Model Filter
      // ==============

      // if (isset($request->code)) {
      //   $employees = $employees->where("code",'like','%'.$request->code.'%');
      // }
      // if (isset($request->name)) {
      //   $employees = $employees->where("name",'like','%'.$request->name.'%');
      // }
      // if (isset($request->admin_id)) {
      //   $employees = $employees->where("admin_id",'like','%'.$request->admin_id.'%');
      // }

      // $employees=$employees->get();


      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        // "x"=>$employees->with('user')->toSql(),
        "data"=>EmployeeResource::collection($employees->with('user')->get()),
      ],200);
    }

    public function store(EmployeeReq $request)
    {



      $employee=new Employee($request->except(['_token']));
      $employee->admin_code=$this->admin->code;
      $new_image = $request->file('photo');

      if($new_image != null){
        $date=new \DateTime();
        $timestamp=$date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/','',$timestamp)).'.'.$ext;
        $location = "/img/employees/{$file_name}";
        try {
          ini_set('memory_limit','256M');
          Image::make($new_image)->save(public_path($location));
        } catch (\Exception $e) {
          throw new MyException("Simpan Foto Gagal");

        }

      } else {
          $location = null;
      }
      // return $location;

      $employee->photo=$location;

      if ($employee->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new EmployeeResource($employee->loadMissing('user'))
        ],200);
      }


    }

    public function update(EmployeeReq $request)
    {
      $code = $request->code;
      $employee = Employee::where("code",$code)->first();
      $employee->admin_code=$this->admin->code;
      $employee->user_code=$request->user_code ?? $employee->user_code;
      $employee->id_number=$request->id_number ?? $employee->id_number;
      $employee->name=$request->name ?? $employee->name;
      $employee->birth_date=$request->birth_date ?? $employee->birth_date;
      $employee->address=$request->address ?? $employee->address;
      $employee->gender=$request->gender ?? $employee->gender;
      $employee->position=$request->position ?? $employee->position;
      $employee->type=$request->type ?? $employee->type;

      $old_image = $location = $employee->photo;
      $photo_preview = $request->photo_preview;

      $new_image = $request->file('photo');

      if($new_image != null){
        $date=new \DateTime();
        $timestamp=$date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/','',$timestamp)).'.'.$ext;
        $location = "/img/employees/{$file_name}";
        ini_set('memory_limit','256M');
        Image::make($new_image)->save(public_path($location));
      }

      if ($new_image == null && $photo_preview == null) {
        $location = null;
      }

      if ($photo_preview==null) {
        if(File::exists(public_path($old_image)) && $old_image != null){
            unlink(public_path($old_image));
        }
      }

      $employee->photo=$location;

      if ($employee->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new EmployeeResource($employee->loadMissing('user'))
        ],200);
      }
    }

    public function show(Request $request)
    {
      $rules = [
         'code' => 'required|min:3|exists:\App\Model\Employee,code',
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

      $data = Employee::where("code",$request->code)->first();
      if (!$data) {
        throw new MyException("Maaf Data Tidak Ditemukan");
      }
      return response()->json([
        "data"=>new EmployeeResource($data->loadMissing('user')),
      ],200);
    }

  public function cetak(Request $request)
  {
          $filename = $request->filename ?? "tx-".MyLib::timestamp();
          $employees = Employee::all();

          // $mime=MyLib::mime("pdf");
          // $pdf = PDF::loadView('laporan.employee', ["data"=>$employees])->setPaper('a4', 'landscape');
          // $bs64=base64_encode($pdf->download($filename.'.pdf'));

          $mime=MyLib::mime("xlsx");
          $bs64=base64_encode(Excel::raw(new EmployeeReport($employees), $mime["exportType"]));

          $result =[
            "contentType"=>$mime["contentType"],
            "data"=>$bs64,
            "dataBase64"=>$mime["dataBase64"].$bs64,
            "filename"=>$filename
          ];

          return $result;
  }


}
