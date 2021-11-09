<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\Journal;

use App\Http\Resources\JournalResource;

use App\Helpers\MyLib;

class Journals extends Controller
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
      $journals = Journal::offset($offset)->limit($limit);

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

        // if (isset($sortList["id"])) {
        //   $journals = $journals->orderBy("id",$sortList["id"]);
        // }

        if (isset($sortList["created_at"])) {
          $journals = $journals->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $journals = $journals->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $journals = $journals->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $journals = $journals->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $journals = $journals->orderBy('id','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      if (isset($request->id)) {
        $journals = $journals->where("id",'like','%'.$request->id.'%');
      }
      // if (isset($request->role_id)) {
      //   $journals = $journals->where("role_id",'like','%'.$request->role_id.'%');
      // }
      // if (isset($request->admin_id)) {
      //   $journals = $journals->where("admin_id",'like','%'.$request->admin_id.'%');
      // }



      $journals=$journals->get();

      return response()->json([
        "data"=>JournalResource::collection($journals),
      ],200);
    }

    public function store()
    {
      $data=new Journal();
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new JournalResource($data)
        ],200);
      }


    }

    // public function update(JournalUpdate $request)
    // {
    //   $id = $request->id;
    //   if ($id=="") {
    //     throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
    //   }
    //
    //   $data = Journal::where("id",$id)->first();
    //   $data->admin_id=$this->admin->id;
    //   $data->code=$request->code ?? $data->code;
    //   $data->title=$request->title ?? $data->title;
    //   $data->side=$request->side ?? $data->side;
    //   $data->balance_sheet_group=$request->balance_sheet_group ?? $data->balance_sheet_group;
    //
    //   if ($data->save()) {
    //     return response()->json([
    //       "message"=>"Data berhasil di ubah",
    //       "data"=>new JournalResource($data)
    //     ],200);
    //   }
    // }


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
