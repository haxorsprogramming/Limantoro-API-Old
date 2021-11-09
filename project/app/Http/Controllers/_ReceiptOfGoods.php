<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\ReceiptOfGood;

use App\Http\Requests\ReceiptOfGoodReq;
use App\Http\Resources\ReceiptOfGoodResource;

use App\Helpers\MyLib;

class ReceiptOfGoods extends Controller
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

      $receiptOfGood = ReceiptOfGood::offset($offset)->limit($limit);

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

        // if (isset($sortList["name"])) {
        //   $receiptOfGood = $receiptOfGood->orderBy("name",$sortList["name"]);
        // }
        //
        // if (isset($sortList["is_finished"])) {
        //   $receiptOfGood = $receiptOfGood->orderBy("is_finished",$sortList["is_finished"]);
        // }
        //
        // if (isset($sortList["created_at"])) {
        //   $receiptOfGood = $receiptOfGood->orderBy("created_at",$sortList["created_at"]);
        // }
        //
        // if (isset($sortList["updated_at"])) {
        //   $receiptOfGood = $receiptOfGood->orderBy("updated_at",$sortList["updated_at"]);
        // }

        // if (isset($sortList["role"])) {
        //   $receiptOfGood = $receiptOfGood->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $receiptOfGood = $receiptOfGood->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $receiptOfGood = $receiptOfGood->orderBy('code','DESC');
      }

      // ==============
      // Model Filter
      // ==============

      if (isset($request->code)) {
        $receiptOfGood = $receiptOfGood->where("code",'like','%'.$request->code.'%');
      }
      // if (isset($request->is_finished)) {
      //   $receiptOfGood = $receiptOfGood->where("is_finished",$request->is_finished);
      // }

      // if (isset($request->admin_id)) {
      //   $users = $users->where("admin_id",'like','%'.$request->admin_id.'%');
      // }

      $receiptOfGood=$receiptOfGood->get();

      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        // "data"=>$employees,
        "data"=>ReceiptOfGoodResource::collection($receiptOfGood),
      ],200);
    }

    public function store(ReceiptOfGoodReq $request)
    {
      $project_id = $request->project_id;

      $project = \App\Model\Project::where("id",$project_id)->where("in_charge_id",$this->admin->id)->first();
      if (!$project) {
        throw new MyException("Maaf Anda tidak dapat memilih project yang bukan tanggung jawab anda");
      }

      $date=new \DateTime();
      $timestamp=$date->format("Y/m/d/His/v");
      $code = "DO/".$this->admin->id."/".$timestamp;

      $data=new ReceiptOfGood($request->except(['id','_token']));
      $data->code = $code;
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new ReceiptOfGoodResource($data),
          "dt"=>$date->format("Y-m-d H:i:s.v")
        ],200);
      }
    }

    public function update(ReceiptOfGoodReq $request)
    {
      $id = $request->id;
      if ($id=="") {
        throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      }

      $project_id = $request->project_id;
      $project = \App\Model\Project::where("id",$project_id)->where("in_charge_id",$this->admin->id)->first();
      if (!$project) {
        throw new MyException("Maaf Anda tidak dapat memilih project yang bukan tanggung jawab anda");
      }

      $data = ReceiptOfGood::where("id",$id)->where("admin_id",$this->admin->id)->first();
      if (!$data) {
        throw new MyException("Maaf Anda tidak dapat mengubah data yang bukan tanggung jawab anda");
      }

      if ($data->vendor_id != $request->vendor_id) {
        $hadChild = \App\Model\ReceiptOfGoodDetail::where("receipt_of_good_id",$data->id)->get();
        if (count($hadChild) > 0  ) {
          throw new MyException("Maaf Data ini berisi detail, sehingga tidak bisa di ubah");
        }
      }

      $data->receipt_date=$request->receipt_date;
      $data->vendor_id=$request->vendor_id;
      $data->project_id=$request->project_id;
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new ReceiptOfGoodResource($data)
        ],200);
      }
    }

    public function show($id,Request $request)
    {
      if (!isset($id)) {
        throw new MyException("Maaf Data input yang dimasukkan kurang lengkap");
      }

      if ($id==0) {
        return response()->json([
          "data"=>[],
        ],200);
      }

      $data = ReceiptOfGood::where("id",$id)->first();

      return response()->json([
        "data"=>new ReceiptOfGoodResource($data),
      ],200);
    }


    // public function updateStatus(Request $request)
    // {
    //
    //   $rules = [
    //      'status'=>[
    //        'required',
    //        Rule::in(['diajukan', 'diterima','ditolak']),
    //      ],
    //   ];
    //
    //   $messages=[
    //      'status.required' => 'Status harus diisi',
    //      'status.in' => 'Format status tidak sesuai',
    //   ];
    //
    //   $validator = \Validator::make($request->all(),$rules,$messages);
    //
    //   if ($validator->fails()) {
    //     throw new ValidationException($validator);
    //   }
    //
    //   $id = $request->id;
    //   if ($id=="") {
    //     throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
    //   }
    //
    //   $data = ReceiptOfGood::where("id",$id)->first();
    //   if (!$data) {
    //     throw new MyException("Harap refresh browser anda");
    //   }
    //   $status=$request->status;
    //   // throw new MyException($status);
    //
    //   // if ($data->status!=="dibuat") {
    //   //   throw new MyException("Maaf Data Ini Sudah Tidak Dapat Diubah Lagi");
    //   // }
    //
    //   if ($status=="diterima") {
    //     $data->approved_at=date("Y-m-d H:i:s");
    //   }
    //
    //   $data->admin_id=$this->admin->id;
    //   $data->status=$status;
    //
    //   if (in_array($status,["diterima","diajukan"])) {
    //     $data->note="";
    //   } else {
    //     $data->note=$request->note;
    //   }
    //
    //   if ($data->save()) {
    //     return response()->json([
    //       "message"=>"Data berhasil di ubah",
    //       "data"=>new ReceiptOfGoodResource($data)
    //     ],200);
    //   }
    // }

    // public function approved(Request $request)
    // {
    //
    //   $id = $request->id;
    //   if ($id=="") {
    //     throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
    //   }
    //
    //   $data = ReceiptOfGood::where("id",$id)->first();
    //   $data->admin_id=$this->admin->id;
    //   $data->name=$request->name ?? $data->name;
    //   $data->address=$request->address ?? $data->address;
    //   $data->phone_number=$request->phone_number ?? $data->phone_number;
    //
    //   if ($data->save()) {
    //     return response()->json([
    //       "message"=>"Data berhasil di ubah",
    //       "data"=>new ReceiptOfGoodResource($data)
    //     ],200);
    //   }
    // }
}
