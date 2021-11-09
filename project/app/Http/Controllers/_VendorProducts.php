<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\VendorProduct;

use App\Http\Requests\VendorProductReq;
use App\Http\Resources\VendorProductResource;

use App\Helpers\MyLib;

class VendorProducts extends Controller
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
      $vendor_products = VendorProduct::offset($offset)->limit($limit);

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

        if (isset($sortList["created_at"])) {
          $vendor_products = $vendor_products->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $vendor_products = $vendor_products->orderBy("updated_at",$sortList["updated_at"]);
        }

        if (isset($sortList["vendor_name"])) {
          $vendor_products = $vendor_products->orderBy(function($q){
            $q->from("vendors")
            ->select("name")
            ->whereColumn("id","vendor_product.vendor_id");
          },$sortList["vendor_id"]);
        }

        if (isset($sortList["product_name"])) {
          $vendor_products = $vendor_products->orderBy(function($q){
            $q->from("products")
            ->select("name")
            ->whereColumn("id","vendor_product.product_id");
          },$sortList["product_id"]);
        }

        if (isset($sortList["admin"])) {
          $vendor_products = $vendor_products->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $vendor_products = $vendor_products->orderBy('id','ASC');
      }

      // ==============
      // Model Filter
      // ==============
      $hasWhere=0;
      if (isset($request->vendor_name)) {
        $vendor_products = $vendor_products->whereIn('vendor_id',function($q)use($request){
          $q->select('id');
          $q->from('vendors');
          $q->where('name','like','%'.$request->vendor_name.'%');
        });
        $hasWhere++;
      }
      if (isset($request->product_name)) {
        $vendor_products = $vendor_products->whereIn('product_id',function($q)use($request){
          $q->select('id');
          $q->from('products');
          $q->where('code','like','%'.$request->product_name.'%');
        });
      }
      if (isset($request->admin_id)) {
        $vendor_products = $vendor_products->where("admin_id",'like','%'.$request->admin_id.'%');
      }

      $vendor_products=$vendor_products->get();


      return response()->json([
        "data"=>VendorProductResource::collection($vendor_products),
      ],200);
    }

    public function store(VendorProductReq $request)
    {

      $alreadyHas = VendorProduct::where("vendor_id",$request->vendor_id)->where("product_id",$request->product_id)->first();
      if ($alreadyHas) {
        throw new MyException("Maaf Produk dengan Vendor yang ini sudah ditambahkan sebelumnya");
      }

      $data=new VendorProduct($request->except(['id','_token']));
      $data->admin_id=$this->admin->id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di tambahkan",
          "data"=>new VendorProductResource($data)
        ],200);
      }
    }

    public function update(VendorProductreq $request)
    {
      $id = $request->id;
      if ($id=="") {
        throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      }

      $alreadyHas = VendorProduct::where("id","!=",$id)->where("vendor_id",$request->vendor_id)->where("product_id",$request->product_id)->first();
      if ($alreadyHas) {
        throw new MyException("Maaf Produk dengan Vendor yang ini sudah ditambahkan sebelumnya");
      }

      $vp = VendorProduct::where("id",$id)->first();

      $used = \App\Model\PurchaseRequestDetailOrder::where("vendor_id",$vp->vendor_id)->where("product_id",$vp->product_id)->first();
      if ($used && $vp->product_id != $request->product_id || $vp->vendor_id != $request->vendor_id ) {
        throw new MyException("Maaf Produk dengan Vendor yang ini sudah digunakan dant tidak dapat diganti lagi");
      }

      $data = VendorProduct::where("id",$id)->first();
      $data->admin_id=$this->admin->id;
      $data->product_id=$request->product_id ?? $data->product_id;
      // $data->price=$request->price ?? $data->price;
      $data->vendor_id=$request->vendor_id ?? $data->vendor_id;

      if ($data->save()) {
        return response()->json([
          "message"=>"Data berhasil di ubah",
          "data"=>new VendorProductResource($data)
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

      $data = VendorProduct::where("id",$id)->first();

      return response()->json([
        "data"=>new VendorProductResource($data),
      ],200);
    }


    public function getVendor(Request $request){
      $rules = [
         'product_id' => 'required|exists:App\Model\Product,id',
      ];

      $messages=[
         'product_id.required' => 'Produk harus di isi',
         'product_id.exists' => 'Produk tidak terdaftar',
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

      $vendorProducts = VendorProduct::offset($offset)->limit($limit);


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

        // if (isset($sortList["code"])) {
        //   $vendorProducts = $vendorProducts->orderBy("code",$sortList["code"]);
        // }
        // if (isset($sortList["name"])) {
        //   $vendorProducts = $vendorProducts->orderBy("name",$sortList["name"]);
        // }
        // if (isset($sortList["type"])) {
        //   $vendorProducts = $vendorProducts->orderBy("type",$sortList["type"]);
        // }
        // if (isset($sortList["brand"])) {
        //   $vendorProducts = $vendorProducts->orderBy("brand",$sortList["brand"]);
        // }
        // if (isset($sortList["specification"])) {
        //   $vendorProducts = $vendorProducts->orderBy("specification",$sortList["specification"]);
        // }

        if (isset($sortList["created_at"])) {
          $vendorProducts = $vendorProducts->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $vendorProducts = $vendorProducts->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $vendorProducts = $vendorProducts->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        if (isset($sortList["admin"])) {
          $vendorProducts = $vendorProducts->orderBy(function($q){
            $q->from("users as u")
            ->select("u.username")
            ->whereColumn("u.id","users.id");
          },$sortList["admin"]);
        }
      }else {
        $vendorProducts = $vendorProducts->orderBy('id','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      // if (isset($request->code)) {
      //   $vendorProducts = $vendorProducts->where("code",'like','%'.$request->code.'%');
      // }
      //
      // if (isset($request->name)) {
      //   $vendorProducts = $vendorProducts->where("name",'like','%'.$request->name.'%');
      // }

      if (isset($request->vendor_name)) {
        $vendorProducts = $vendorProducts->where('vendor_id',function($q)use($request){
          $q->select('id');
          $q->from('vendors');
          $q->where('name','like','%'.$request->vendor_name.'%');
        });
      }


      // Words => Kata/Kalimat yang akan dicari
      // $req_words = $request->words;
      // if ($req_words) {
      //   $datas = $datas->where('vendor_id',function($q)use($req_words){
      //     $q->select('id');
      //     $q->from('vendors');
      //     $q->where('name','like','%'.$req_words.'%');
      //   });
      // }

      $vendorProducts=$vendorProducts->where("product_id",$request->product_id)->get();

      foreach ($vendorProducts as $key => $vendorProduct) {
        $prd = \App\Model\PurchaseRequestDetailOrder::where("product_id",$vendorProduct->product_id)
        ->where("vendor_id",$vendorProduct->vendor_id)
        ->orderBy("updated_at","desc")
        ->first();

        $vendorProduct["last_price"]=($prd)?$prd->price:0;
        // array_push($results,$vp);
      }



      return response()->json([
        // "data"=>\App\Http\Resources\VendorResource::collection($coll),
        "data"=>VendorProductResource::collection($vendorProducts),
      ],200);
    }

    public function getVendorProduct(Request $request)
    {

      $rules = [
         'vendor_id'=>'required|exists:App\Model\Vendor,id',
         'product_id'=>'required|exists:App\Model\Product,id'
      ];

      $messages=[
         'vendor_id.required' => 'Vendor di perlukan',
         'vendor_id.exists' => 'Vendor tidak terdaftar',

         'product_id.required' => 'Produk di perlukan',
         'product_id.exists' => 'Produk tidak terdaftar',
      ];


      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $vendor_id = $request->vendor_id;
      $product_id = $request->product_id;


      $data = VendorProduct::where("product_id",$product_id)->where("vendor_id",$vendor_id)->first();
      if (!$data) {
        throw new MyException("data tidak ditemukan");
      }

      $prd = \App\Model\PurchaseRequestDetailOrder::where("product_id",$data->product_id)
      ->where("vendor_id",$data->vendor_id)
      ->orderBy("updated_at","desc")
      ->first();

      $data["last_price"]=($prd)?$prd->price:0;

      return response()->json([
        "data"=>new VendorProductResource($data),
      ],200);
    }
}
