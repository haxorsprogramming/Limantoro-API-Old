<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\Journal;
use App\Model\JournalDetail;

use App\Http\Requests\JournalDetailStore;
use App\Http\Requests\JournalDetailStoreMsg;

use App\Http\Requests\JournalDetailReq;

use App\Http\Resources\JournalDetailResource;

use App\Helpers\MyLib;
use Illuminate\Validation\Rule;

class JournalDetails extends Controller
{
    private $admin;

    public function __construct()
    {
        $this->admin = MyLib::admin();
    }

    public function index(Request $request)
    {
      $rules = [
         'journal_id'=>'required|exists:App\Model\Journal,id',
      ];

      $messages=[
        'journal_id.required' => 'Mohon Refresh Browser Anda',
        'journal_id.exists' => 'Mohon Refresh Browser Anda',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new MyException($validator->messages());
      }

      $journal_id = $request->journal_id;

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
      $journal_details = JournalDetail::where('journal_id',$journal_id)->offset($offset)->limit($limit);
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
          $journal_details = $journal_details->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $journal_details = $journal_details->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $journal_details = $journal_details->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $journal_details = $journal_details->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $journal_details = $journal_details->orderBy('id','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      if (isset($request->id)) {
        $journal_details = $journal_details->where("id",'like','%'.$request->id.'%');
      }
      // if (isset($request->role_id)) {
      //   $journals = $journals->where("role_id",'like','%'.$request->role_id.'%');
      // }
      // if (isset($request->admin_id)) {
      //   $journals = $journals->where("admin_id",'like','%'.$request->admin_id.'%');
      // }


      $journal_details=$journal_details->get();

      return response()->json([
        "data"=>JournalDetailResource::collection($journal_details),
      ],200);
    }

    public function store(JournalDetailReq $request)
    {

      // if ($request->validator && $request->validator->fails()) { // fungsi untuk ngecek apakah validasi gagal
      //   throw new MyException($request->validator->messages()->all()[0]);
      // }
      $jdsm =  new JournalDetailStoreMsg;
      $validator = \Validator::make($request->all(),$jdsm->rules(),$jdsm->messages());
      if ($validator->fails()) {
          throw new MyException($validator->messages()->all()[0]);
      }

      $data=new JournalDetail();
      $data->admin_id=$this->admin->id;
      $data->journal_id=$request->journal_id;
      $data->chart_of_account_id=$request->chart_of_account_id;
      $data->description=$request->description;
      $data->ref=$request->ref;
      $data->debit=$request->debit;
      $data->credit=$request->credit;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new JournalDetailResource($data)
        ],200);
      }
    }

    public function update(JournalDetailReq $request)
    {

      // if ($request->validator && $request->validator->fails()) { // fungsi untuk ngecek apakah validasi gagal
      //   throw new MyException($request->validator->messages()->all()[0]);
      // }
      $jdsm =  new JournalDetailStoreMsg;
      $validator = \Validator::make($request->all(),$jdsm->rules(),$jdsm->messages());
      if ($validator->fails()) {
          throw new MyException($validator->messages()->all()[0]);
      }

      $data=JournalDetail::where("id",$request->id)->first();
      if (!$data) {
        throw new MyException("Data tidak ditemukan");
      }
      $data->admin_id=$this->admin->id;
      $data->journal_id=$request->journal_id;
      $data->chart_of_account_id=$request->chart_of_account_id;
      $data->description=$request->description;
      $data->ref=$request->ref;
      $data->debit=$request->debit;
      $data->credit=$request->credit;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new JournalDetailResource($data)
        ],200);
      }
    }

    public function show($id,Request $request)
    {

      $rules = [
         'journal_id' => 'required|exists:App\Model\Journal,id',
      ];

      $messages=[
         'journal_id.required' => 'Nomor ID Jurnal Harus Ada',
         'journal_id.exists' => 'Nomor ID Jurnal tidak terdaftar',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new MyException($validator->messages()->all());
      }

      if (!isset($id)) {
        throw new MyException("Maaf Data input yang dimasukkan kurang lengkap");
      }

      if ($id==0) {
        return response()->json([
          "data"=>[],
        ],200);
      }

      $data = JournalDetail::where("id",$id)->first();

      return response()->json([
        "data"=>new JournalDetailResource($data),
      ],200);
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
