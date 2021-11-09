<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\ProductRequest;
use App\Model\ProductRequest;

use App\Http\Requests\ProductRequestReq;
use App\Http\Resources\ProductRequestResource;

use App\Helpers\MyLib;

class ProductRequests extends Controller
{
    private $admin;

    public function __construct()
    {
        $this->admin = MyLib::admin();

    }

    public function callCekRole()
    {
      if ( !in_array($this->admin->role->title,["Developer","Manager Lapangan"]) ) {
        throw new MyException("Maaf Anda Tidak Punya Izin Akses");
      }
    }

    public function index(Request $request)
    {

      // $rules = [
      //    'page' => 'required|numeric',
      //    'type'=>[
      //      'required',
      //      Rule::in(['limit', 'all']),
      //    ],
      //    'purchase_request_id'=>'required|exists:App\Model\ProductRequest,id'
      // ];

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
      $productRequest = ProductRequest::offset($offset)->limit($limit);
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

        // if (isset($sortList["id_number"])) {
        //   $employees = $employees->orderBy("id_number",$sortList["id_number"]);
        // }
        //
        // if (isset($sortList["name"])) {
        //   $employees = $employees->orderBy("name",$sortList["name"]);
        // }

        if (isset($sortList["created_at"])) {
          $productRequest = $productRequest->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $productRequest = $productRequest->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $productRequest = $productRequest->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $productRequest = $productRequest->orderBy('id','DESC');
      }

      // ==============
      // Model Filter
      // ==============

      // if (isset($request->id_number)) {
      //   $employees = $employees->where("id_number",'like','%'.$request->id_number.'%');
      // }
      // if (isset($request->name)) {
      //   $employees = $employees->where("name",'like','%'.$request->name.'%');
      // }
      // if (isset($request->admin_id)) {
      //   $employees = $employees->where("admin_id",'like','%'.$request->admin_id.'%');
      // }

      // Words => Kata/Kalimat yang akan dicari
      // $req_words = $request->words;
      // if ($req_words) {
      //   $datas = $datas->where('item','like','%'.$req_words.'%')
      //   ->orWhere('qty','like','%'.$req_words.'%')
      //   ->orWhere('note','like','%'.$req_words.'%');
      //   // ->orWhere('phone_number','like','%'.$req_words.'%');
      // }
      $productRequest=$productRequest->get();


      return response()->json([
        "data"=>ProductRequestResource::collection($productRequest),
      ],200);
    }

    public function store(ProductRequestReq $request)
    {
      $this->callCekRole();

      $check = ProductRequest::find($request->purchase_request_id);
      if (in_array($check->status,["dianjurkan","diterima"])) {
        throw new MyException("Maaf PO tidak dapat ditambahkan");
      }

      $notunique = ProductRequest::where("purchase_request_id",$request->purchase_request_id)->where("product_id",$request->product_id)->first();
      if ($notunique) {
        throw new MyException("Maaf Produk sudah di tambahkan sebelum nya");
      }

      $data=new ProductRequest($request->except(['id','_token']));
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new ProductRequestResource($data)
        ],200);
      }
    }

    public function update(ProductRequestReq $request)
    {
      $this->callCekRole();

      $id = $request->id;
      if ($id=="") {
        throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      }

      $check = ProductRequest::find($request->purchase_request_id);
      if (in_array($check->status,["dianjurkan","diterima"])) {
        throw new MyException("Maaf PO tidak dapat diubah");
      }

      $notunique = ProductRequest::where("id","!=",$id)->where("purchase_request_id",$request->purchase_request_id)->where("product_id",$request->product_id)->first();
      if ($notunique) {
        throw new MyException("Maaf Produk sudah di tambahkan sebelum nya");
      }

      $data = ProductRequest::where("id",$id)->first();
      $data->admin_id=$this->admin->id;
      $data->qty=$request->qty;
      $data->note=$request->note;
      $data->product_id=$request->product_id;


      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new ProductRequestResource($data)
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

      $data = ProductRequest::where("id",$id)->first();

      return response()->json([
        "data"=>new ProductRequestResource($data),
      ],200);
    }

    public function purchase_request_orders(Request $request)
    {


      $rules = [
         'page' => 'required|numeric',
         'type'=>[
           'required',
           Rule::in(['limit', 'all']),
         ],
         'purchase_order_id'=>'required|exists:App\Model\PurchaseOrder,id'
      ];

      $messages=[
         'page.required' => 'No halaman di perlukan',
         'page.numeric' => 'No halaman harus berupa angka',

         'type.required' => 'Tipe pengambilan data diperlukan',
         'type.in' => 'Tipe pengambilan data limit atau all',

         'purchase_order_id.required' => 'purchase order id di perlukan',
         'purchase_order_id.exists' => 'purchase order id tidak terdaftar',

      ];


      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $type=$request->type;
      $limit=15;



      if ($type=="limit") {
        $page = $request->page ?? 1;

        $req_limit = $request->limit;
        if ($req_limit && $req_limit > 50) {
          throw new MyException("Maaf Batas Pengambilan Data Per Halaman Maksimal adalah 50 Baris Data");
        }elseif ($req_limit && $req_limit <= 50) {
          $limit = $req_limit;
        }

        // OFFSET => Memuat halaman dari data ke berapa
        $offset = 0;
        $offset = ($page*$limit)-$limit;

        $datas = ProductRequest::offset($offset)->limit($limit)->orderBy('id','DESC');
      }else {
        $datas = ProductRequest::orderBy('id','DESC');
      }

      $purchase_order = \App\Model\PurchaseOrder::where("id",$request->purchase_order_id)->first();

      $datas = $datas->where('purchase_request_id',$purchase_order->purchase_request_id);



      // Words => Kata/Kalimat yang akan dicari
      // $req_words = $request->words;
      // if ($req_words) {
      //   $datas = $datas->where('item','like','%'.$req_words.'%')
      //   ->orWhere('qty','like','%'.$req_words.'%')
      //   ->orWhere('price','like','%'.$req_words.'%');
      //   // ->orWhere('phone_number','like','%'.$req_words.'%');
      // }
      $datas=$datas->get();


      $record=count($datas);
      // if -1 (No Record / Not Found) 0 (No More Record) > 0 (Have Record)
      if($type=="limit" && $page==1 && $record==0){
        $record=-1;
      }




      return response()->json([
        // "data"=>EmployeeResource::collection($employees->keyBy->id),
        // "data"=>$employees,
        "data"=>ProductRequestResource::collection($datas),
        "record"=>$record,
        "limit"=>$limit
      ],200);
    }

}
