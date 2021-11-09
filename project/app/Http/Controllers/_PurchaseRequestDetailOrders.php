<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\PurchaseRequestDetailOrder;

use App\Http\Requests\PurchaseRequestDetailOrderReq;
use App\Http\Resources\PurchaseRequestDetailOrderResource;

use App\Helpers\MyLib;

class PurchaseRequestDetailOrders extends Controller
{
    private $admin;

    public function __construct()
    {
        $this->admin = MyLib::admin();

    }

    public function callCekRole()
    {
      if ( !in_array($this->admin->role->title,["Developer","Purchasing"]) ) {
        throw new MyException("Maaf Anda Tidak Punya Izin Akses");
      }
    }

    public function index(Request $request)
    {


      $rules = [
         'purchase_request_detail_id'=>'required|exists:App\Model\PurchaseRequestDetail,id'
      ];

      $messages=[
         'purchase_request_detail_id.required' => 'Purchase request detail id di perlukan',
         'purchase_request_detail_id.exists' => 'Purchase request detail id tidak terdaftar',

      ];


      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

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

      $purchaseRequestDetailOrder = PurchaseRequestDetailOrder::offset($offset)->limit($limit);


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
        //   $projects = $projects->orderBy("name",$sortList["name"]);
        // }
        //
        // if (isset($sortList["is_finished"])) {
        //   $projects = $projects->orderBy("is_finished",$sortList["is_finished"]);
        // }
        //
        // if (isset($sortList["created_at"])) {
        //   $projects = $projects->orderBy("created_at",$sortList["created_at"]);
        // }
        //
        // if (isset($sortList["updated_at"])) {
        //   $projects = $projects->orderBy("updated_at",$sortList["updated_at"]);
        // }

        // if (isset($sortList["role"])) {
        //   $projects = $projects->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $purchaseRequestDetailOrder = $purchaseRequestDetailOrder->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $purchaseRequestDetailOrder = $purchaseRequestDetailOrder->orderBy('id','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      // if (isset($request->name)) {
      //   $projects = $projects->where("name",'like','%'.$request->name.'%');
      // }
      // if (isset($request->is_finished)) {
      //   $projects = $projects->where("is_finished",$request->is_finished);
      // }

      // if (isset($request->admin_id)) {
      //   $users = $users->where("admin_id",'like','%'.$request->admin_id.'%');
      // }

      // Words => Kata/Kalimat yang akan dicari
      // $req_words = $request->words;
      // if ($req_words) {
      //   // $datas = $datas->where('item','like','%'.$req_words.'%')
      //   // ->orWhere('qty','like','%'.$req_words.'%')
      //   // ->orWhere('price','like','%'.$req_words.'%');
      //   // ->orWhere('phone_number','like','%'.$req_words.'%');
      // }
      $purchaseRequestDetailOrder=$purchaseRequestDetailOrder->where("purchase_request_detail_id",$request->purchase_request_detail_id)->get();

      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        // "data"=>$employees,
        "data"=>PurchaseRequestDetailOrderResource::collection($purchaseRequestDetailOrder),
      ],200);
    }

    public function store(PurchaseRequestDetailOrderReq $request)
    {
      // throw new MyException("test");

      $this->callCekRole();

      $hasIn = \App\Model\VendorProduct::where("product_id",$request->product_id)->where("vendor_id",$request->vendor_id)->first();
      if (!$hasIn) {
        throw new MyException("Maaf Vendor dan produk ini tidak terdaftar");
      }

      $alreadyHas = PurchaseRequestDetailOrder::where("purchase_request_detail_id",$request->purchase_request_detail_id)->where("product_id",$request->product_id)->where("vendor_id",$request->vendor_id)->first();
      if ($alreadyHas) {
        throw new MyException("Maaf Vendor ini sudah ditambahkan sebelumnya");
      }

      $data=new PurchaseRequestDetailOrder($request->except(['id','_token']));
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new PurchaseRequestDetailOrderResource($data)
        ],200);
      }
    }

    public function update(PurchaseRequestDetailOrderReq $request)
    {
      $this->callCekRole();

      $id = $request->id;
      if ($id=="") {
        throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      }

      $hasIn = \App\Model\VendorProduct::where("product_id",$request->product_id)->where("vendor_id",$request->vendor_id)->first();
      if (!$hasIn) {
        throw new MyException("Maaf Vendor dan produk ini tidak terdaftar");
      }

      $alreadyHas = PurchaseRequestDetailOrder::where("id","!=",$id)->where("purchase_request_detail_id",$request->purchase_request_detail_id)->where("product_id",$request->product_id)->where("vendor_id",$request->vendor_id)->first();
      if ($alreadyHas) {
        throw new MyException("Maaf Vendor ini sudah ditambahkan sebelumnya");
      }

      $data = PurchaseRequestDetailOrder::where("id",$id)->first();
      $data->admin_id=$this->admin->id;
      $data->purchase_request_detail_id=$request->purchase_request_detail_id ?? $data->purchase_request_detail_id;
      $data->vendor_id=$request->vendor_id ?? $data->vendor_id;
      $data->product_id=$request->product_id ?? $data->product_id;
      $data->qty=$request->qty ?? $data->qty;
      $data->price=$request->price ?? $data->price;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new PurchaseRequestDetailOrderResource($data)
        ],200);
      }
    }

    public function show($id,Request $request)
    {
      $this->callCekRole();

      if (!isset($id)) {
        throw new MyException("Maaf Data input yang dimasukkan kurang lengkap");
      }

      if ($id==0) {
        return response()->json([
          "data"=>[],
        ],200);
      }

      $data = PurchaseRequestDetailOrder::where("id",$id)->first();

      return response()->json([
        "data"=>new PurchaseRequestDetailOrderResource($data),
      ],200);
    }


    // public function approved(Request $request)
    // {
    //
    //   $id = $request->id;
    //   if ($id=="") {
    //     throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
    //   }
    //
    //   $data = PurchaseRequestDetailOrder::where("id",$id)->first();
    //   $data->admin_id=$this->admin->id;
    //   $data->name=$request->name ?? $data->name;
    //   $data->address=$request->address ?? $data->address;
    //   $data->phone_number=$request->phone_number ?? $data->phone_number;
    //
    //   if ($data->save()) {
    //     return response()->json([
    //       "message"=>"Data berhasil di ubah",
    //       "data"=>new PurchaseRequestDetailOrderResource($data)
    //     ],200);
    //   }
    // }
}
