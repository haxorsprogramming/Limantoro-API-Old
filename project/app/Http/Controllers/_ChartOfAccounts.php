<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Excel;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\ChartOfAccount;

use App\Http\Requests\ChartOfAccountStore;
use App\Http\Requests\ChartOfAccountUpdate;
use App\Http\Resources\ChartOfAccountResource;
use App\Exports\EmployeeReport;

use App\Helpers\MyLib;
use Image;
use File;
use Illuminate\Validation\Rule;

class ChartOfAccounts extends Controller
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
      $chartOfAccounts = ChartOfAccount::offset($offset)->limit($limit);

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
          $chartOfAccounts = $chartOfAccounts->orderBy("code",$sortList["code"]);
        }

        if (isset($sortList["title"])) {
          $chartOfAccounts = $chartOfAccounts->orderBy("title",$sortList["title"]);
        }

        if (isset($sortList["created_at"])) {
          $chartOfAccounts = $chartOfAccounts->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $chartOfAccounts = $chartOfAccounts->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $chartOfAccounts = $chartOfAccounts->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $chartOfAccounts = $chartOfAccounts->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $chartOfAccounts = $chartOfAccounts->orderBy('id','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      if (isset($request->code)) {
        $chartOfAccounts = $chartOfAccounts->where("code",'like','%'.$request->code.'%');
      }
      if (isset($request->title)) {
        $chartOfAccounts = $chartOfAccounts->where("title",'like','%'.$request->title.'%');
      }
      if (isset($request->admin_id)) {
        $chartOfAccounts = $chartOfAccounts->where("admin_id",'like','%'.$request->admin_id.'%');
      }


      $chartOfAccounts=$chartOfAccounts->get();


      return response()->json([
        "data"=>ChartOfAccountResource::collection($chartOfAccounts),
      ],200);
    }

    public function store(ChartOfAccountStore $request)
    {
      $data=new ChartOfAccount($request->except(['id','_token']));
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new ChartOfAccountResource($data)
        ],200);
      }


    }

    public function update(ChartOfAccountUpdate $request)
    {
      $id = $request->id;
      if ($id=="") {
        throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      }

      $data = ChartOfAccount::where("id",$id)->first();
      $data->admin_id=$this->admin->id;
      $data->code=$request->code ?? $data->code;
      $data->title=$request->title ?? $data->title;
      $data->side=$request->side ?? $data->side;
      $data->balance_sheet_group=$request->balance_sheet_group ?? $data->balance_sheet_group;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new ChartOfAccountResource($data)
        ],200);
      }
    }
    public function show($id,Request $request)
    {
      if (!isset($id)) {
        throw new MyException("Maaf Data input yang dimasukkan kurang lengkap");
      }

      if ($id=="") {
        return response()->json([
          "data"=>[],
        ],200);
      }

      $data = ChartOfAccount::where("id",$id);
      if (!$data->first()) {
        throw new MyException("Maaf Data Tidak Ditemukan");
      }
      return response()->json([
        "data"=>new ChartOfAccountResource($data->first()),
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
