<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\PurchaseOrder;

use App\Http\Requests\PurchaseOrderReq;
use App\Http\Resources\PurchaseOrderResource;

use App\Helpers\MyLib;
use DB;
use PDF;

class PurchaseOrders extends Controller
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
      $purchaseRequest = PurchaseOrder::offset($offset)->limit($limit);

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

        // if (isset($sortList["created_at"])) {
        //   $purchaseRequest = $purchaseRequest->orderBy("created_at",$sortList["created_at"]);
        // }
        //
        // if (isset($sortList["updated_at"])) {
        //   $purchaseRequest = $purchaseRequest->orderBy("updated_at",$sortList["updated_at"]);
        // }

        // if (isset($sortList["role"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $purchaseRequest = $purchaseRequest->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $purchaseRequest = $purchaseRequest->orderBy('number','DESC');
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

        if (isset($likeList["number"])) {
          $purchaseRequest = $purchaseRequest->where("number","like",$likeList["number"]);
        }

        if (isset($likeList["purchase_request_number"])) {
          $purchaseRequest = $purchaseRequest->orWhere("purchase_request_number","like",$likeList["purchase_request_number"]);
        }

        if (isset($likeList["proof_of_expenditure_number"])) {
          $purchaseRequest = $purchaseRequest->orWhere("purchase_request_number","like",$likeList["purchase_request_number"]);
        }


      }
      // Words => Kata/Kalimat yang akan dicari
      // $req_words = $request->words;
      // if ($req_words) {
      //   $datas = $datas->where('created_by',function($q)use($req_words){
      //     $q->select('id');
      //     $q->from('users');
      //     $q->where('username','like','%'.$req_words.'%');
      //   })
      //   ->orWhere('id','like','%'.(int)$req_words.'%')
      //   ->orWhereRaw('CONCAT("PR-",LPAD(`id`,10,"0")) like ?',['%'.$req_words.'%']);
      // }
      // $purchaseRequest=$purchaseRequest->get();


      return response()->json([
        "data"=>PurchaseOrderResource::collection($purchaseRequest->with([
          'approver'=>function($q){
            $q->with(['employee']);
          },
          'supplier',
          'purchase_request',
          'locker'
        ])->get()),
      ],200);
    }

    public function store(PurchaseOrderReq $request)
    {
      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      }

      DB::beginTransaction();
      try {
        $front = "PO.".substr(date("Y"),-2)."-";
        $pr=PurchaseOrder::where("number",'like',$front.'%')->orderBy("created_at","desc")->first();
        if ($pr) {
          $number = $front.str_pad((int)substr($pr->number,6)+1, 4, "0", STR_PAD_LEFT);
        }else {
          $number = $front."0001";
        }
        $admin_code = $this->admin->code;
        $purchase_request_number=$request->purchase_request_number;

        $data=new PurchaseOrder();
        $data->admin_code=$admin_code;
        $data->number=$number;
        $data->date=$request->date;
        $data->purchase_request_number=$purchase_request_number;
        $data->supplier_code=$request->supplier_code;
        // $data->proof_of_payment_number=$request->proof_of_payment_number;
        // $data->approver_code=$request->approver_code;
        $data->save();

        $purchase_order_detail=[];
        if (!$request->purchase_order_details) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $purchase_order_details = json_decode($request->purchase_order_details,true);
        if (count($purchase_order_details)==0) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $compareItems=$this->compareItems($purchase_request_number);

        $materials= [];
        foreach ($purchase_order_details as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            'qty' => 'required|min:1|numeric',
            // 'price' => 'required|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            'qty.required' => 'Quantity yang diminta tidak boleh kosong',
            'qty.min' => 'Quantity yang diminta minimal 1',
            'qty.numeric' => 'Quantity yang diminta harus angka',

            // 'price.required' => 'Harga tidak boleh kosong',
            // 'price.numeric' => 'Harga harus angka',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }

          $material_code = $value["material_code"];

          if ($value['qty'] > $compareItems[$material_code] ) {
            throw new \Exception("Baris Data Ke-".$ordinal." "."Qty tidak boleh lebih dari ".$compareItems[$material_code]);
          }

          if (in_array($material_code,$materials)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan tidak boleh sama");
          }

          array_push($materials,$material_code);

          $purchase_order_detail = new \App\Model\PurchaseOrderDetail();
          $purchase_order_detail->admin_code = $admin_code;
          $purchase_order_detail->purchase_order_number = $number;
          $purchase_order_detail->ordinal = $ordinal;
          $purchase_order_detail->material_code = $value['material_code'];
          $purchase_order_detail->qty = $value['qty'];
          $purchase_order_detail->price = (float)$value['price'];
          $purchase_order_detail->note = $value['note'];
          $purchase_order_detail->save();
        }


        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage(),400);
      }

    }

    public function update(PurchaseOrderReq $request)
    {

      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Mengubah Data");
      }

      if (count(\App\Model\GoodsReceipt::where("purchase_order_number",$request->number)->get())) {
        throw new MyException("Maaf Data Sudah Digunakan dan tidak dapat di ubah lagi");
      }


      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $purchase_request_number = $request->purchase_request_number;
        $number = $request->number;

        $data=PurchaseOrder::where('number',$number)->first();

        if ($data->approver_code) {
          throw new \Exception("PO sudah disetujui, tidak dapat diubah lagi");
        }
        $data->admin_code=$admin_code;
        $data->date=$request->date;
        $data->purchase_request_number=$purchase_request_number;
        $data->supplier_code=$request->supplier_code;
        // $data->proof_of_payment_number=$request->proof_of_payment_number;
        // $data->approver_code=$request->approver_code;
        $data->save();

        $purchase_order_detail=[];
        if (!$request->purchase_order_details) {
          throw new \Exception("Silahkan masukkan data detail");
        }
        $purchase_order_details = json_decode($request->purchase_order_details,true);
        if (count($purchase_order_details)==0) {
          throw new \Exception("Silahkan masukkan data detail");
        }
        // if (\App\Model\PurchaseOrderDetail::where("approved_qty",">",0)->where("purchase_order_number",$number)->first()) {
        //   throw new \Exception("Maaf purchase request sudah di approved , data sudah tidak dapat di ubah");
        // }

        $compareItems=$this->compareItems($purchase_request_number,$number);

        $materials=[];
        \App\Model\PurchaseOrderDetail::where("purchase_order_number",$number)->delete();
        foreach ($purchase_order_details as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            'qty' => 'required|min:1|numeric',
            // 'price' => 'required|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            'qty.required' => 'Quantity yang diminta tidak boleh kosong',
            'qty.min' => 'Quantity yang diminta minimal 1',
            'qty.numeric' => 'Quantity yang diminta harus angka',

            // 'price.required' => 'Harga tidak boleh kosong',
            // 'price.numeric' => 'Harga harus berupa angka',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }


          $material_code = $value["material_code"];

          if ($value['qty'] > $compareItems[$material_code] ) {
            throw new \Exception("Baris Data Ke-".$ordinal." "."Qty tidak boleh lebih dari ".$compareItems[$material_code]);
          }

          if (in_array($material_code,$materials)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan sudah terdaftar");
          }
          array_push($materials,$material_code);

          $purchase_order_detail = new \App\Model\PurchaseOrderDetail();
          $purchase_order_detail->admin_code = $admin_code;
          $purchase_order_detail->purchase_order_number = $number;
          $purchase_order_detail->ordinal = $ordinal;
          $purchase_order_detail->material_code = $value['material_code'];
          $purchase_order_detail->qty = $value['qty'];
          $purchase_order_detail->price = (float)$value['price'];
          $purchase_order_detail->note = $value['note'];
          $purchase_order_detail->save();

        }

        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage(),400);
      }

    }

    public function show(Request $request)
    {
      $data = new PurchaseOrder();

      $data=$data->where("number",$request->number);

      $data=$data->with([
        'approver'=>function($q){
          $q->with(['employee']);
        },
        'supplier',
        'purchase_request',
        'purchase_order_details'=>function($q){
          $q->orderBy("ordinal","asc");
          $q->with(['material']);
        }
      ])->first();

      if (!$data) {
          throw new MyException("Maaf Data Tidak Ditemukan");
      }

      return response()->json([
        "data"=>new PurchaseOrderResource($data),
      ],200);
    }


    // public function setApprove(Request $request)
    // {
    //
    //   if (!in_array($this->admin->role->title,["Owner","Developer"])) {
    //     throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menentukan qty yang disetujui");
    //   }
    //
    //   $rules = [
    //     'number' => 'required|exists:App\Model\PurchaseOrder,number',
    //     'is_approved' => [
    //       Rule::in(['0', '1']),
    //     ],
    //   ];
    //
    //   $messages=[
    //     'number.required' => 'PR No harus ada',
    //     'number.exists' => 'PR No tidak terdaftar',
    //     'is_approved.in' => 'Approve harus di pilih',
    //
    //   ];
    //
    //   $validator = \Validator::make($request->all(),$rules,$messages);
    //   if ($validator->fails()) {
    //     throw new ValidationException($validator);
    //   }
    //
    //   DB::beginTransaction();
    //   try {
    //     $admin_code = $this->admin->code;
    //     $number = $request->number;
    //     $is_approved = $request->is_approved;
    //
    //     $data=PurchaseOrder::where('number',$number)->first();
    //     $data->approver_code=($is_approved==0)?null:$admin_code;
    //     $data->admin_code=$admin_code;
    //     $data->save();
    //
    //     DB::commit();
    //
    //     return response()->json([
    //       "message"=>"done"
    //     ],200);
    //
    //   } catch (\Exception $e) {
    //     DB::rollback();
    //     throw new MyException($e->getMessage(),400);
    //   }
    //
    //
    // }

    public function compareItems($purchase_request_number,$purchase_order_number=null)
    {
      $compareItems=[];

      $purchase_request=\App\Model\PurchaseRequest::where("number",$purchase_request_number)->first();
      foreach ($purchase_request->purchase_request_details as $key => $purchase_request_detail) {
        if (!array_key_exists($purchase_request_detail->material_code,$compareItems)) {
          $compareItems[$purchase_request_detail->material_code]=$purchase_request_detail->approved_qty;
        }
      }

      if ($purchase_order_number==null) {
        $purchase_order_details=\App\Model\PurchaseOrderDetail::whereIn("purchase_order_number",
        function($q)use($purchase_request_number){
          $q->select("number");
          $q->from('purchase_orders');
          $q->where('purchase_request_number',$purchase_request_number);
        })->get();
      }else {
        $purchase_order_details=\App\Model\PurchaseOrderDetail::whereIn("purchase_order_number",
        function($q)use($purchase_request_number,$purchase_order_number){
          $q->select("number");
          $q->from('purchase_orders');
          $q->where('number',"!=",$purchase_order_number);
          $q->where('purchase_request_number',$purchase_request_number);
        })->get();
      }

      foreach ($purchase_order_details as $key => $purchase_order_detail) {
        $calc=$compareItems[$purchase_order_detail->material_code] - $purchase_order_detail->qty;
        $compareItems[$purchase_order_detail->material_code] = $calc;
      }

      return $compareItems;

    }

    public function getAvailableQty(Request $request)
    {

      // if (!in_array($this->admin->role->title,["Owner","Developer"])) {
      //   throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menentukan qty yang disetujui");
      // }

      $rules = [
        'supplier_code' => 'required|exists:App\Model\Supplier,code',
      ];

      $messages=[
        'supplier_code.required' => 'Kode Supplier harus ada',
        'supplier_code.exists' => 'Kode Supplier tidak terdaftar',
        // 'is_approved.in' => 'Approve harus di pilih',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $admin_code = $this->admin->code;
      $supplier_code = $request->supplier_code;
      $exclude_gr_no = $request->goods_receipt_number;

      $purchaseOrders=PurchaseOrder::where('supplier_code',$supplier_code)->orderBy("number","desc")->with([
        'approver'=>function($q){
          $q->with(['employee']);
        },
        'supplier',
        'purchase_request',
        'purchase_order_details'=>function($q){
          $q->with('material');
        }
      ])->get();

      $data=[];
      foreach ($purchaseOrders as $key => $purchaseOrder) {

        $purchase_order_details=[];

        foreach ($purchaseOrder->purchase_order_details as $key => $purchase_order_detail) {
          array_push($purchase_order_details,[
            "purchase_order_number"=>$purchase_order_detail->purchase_order_number,
            "ordinal"=>$purchase_order_detail->ordinal,
            "material"=>$purchase_order_detail->material,
            "po_qty"=>$purchase_order_detail->qty,
            "gr_qty"=>0,
            "rn_qty"=>0,
          ]);
        }

        $goods_receipt_details = \App\Model\GoodsReceiptDetail::whereIn("goods_receipt_number",function($q)use($purchaseOrder,$exclude_gr_no){
          $q->select('number')->from('goods_receipts');
          $q->where("purchase_order_number",$purchaseOrder->number);
          $q->where("supplier_code",$purchaseOrder->supplier_code);
          if ($exclude_gr_no) {
            $q->where("number","!=",$exclude_gr_no);
          }
        })->get();

        // throw new MyException($purchase_order_details);


        foreach ($goods_receipt_details as $key => $goods_receipt_detail) {
          $indexOf = array_search($goods_receipt_detail->material->code,array_map(function($q){
            return $q["material"]["code"];
          },$purchase_order_details));

          $purchase_order_details[$indexOf]["gr_qty"]+=$goods_receipt_detail->qty;
        }


        $purchase_return_details = \App\Model\PurchaseReturnDetail::whereIn("purchase_return_number",function($q)use($purchaseOrder){
          $q->select('number')->from('purchase_returns');
          $q->where("purchase_order_number",$purchaseOrder->number);
          $q->where("supplier_code",$purchaseOrder->supplier_code);
        })->get();

        foreach ($purchase_return_details as $key => $purchase_return_detail) {
          $indexOf = array_search($purchase_return_detail->material->code,array_map(function($q){
            return $q["material"]["code"];
          },$purchase_order_details));

          $purchase_order_details[$indexOf]["rn_qty"]+=$purchase_return_detail->qty;
        }

        // if ($exclude_gr_no) {
        //   $goods_receipts = \App\Model\GoodsReceiptDetail::whereIn("number",function($q)use($purchaseOrder,$exclude_gr_no){
        //     $q->select('number')->from('goods_receipt_details');
        //     $q->where("purchase_order_number",$purchaseOrder->number);
        //     $q->where("supplier_code",$purchaseOrder->supplier_code);
        //   })->get();
        // }else {
        //   $goods_receipts = \App\Model\GoodsReceiptDetail::whereIn("number",function($q)use($purchaseOrder,$exclude_gr_no){
        //     $q->select('number')->from('goods_receipt_details');
        //     $q->where("purchase_order_number",$purchaseOrder->number);
        //     $q->where("supplier_code",$purchaseOrder->supplier_code);
        //   })->get();
        // }

        array_push($data,[
          'number' => $purchaseOrder->number,
          'date' => $purchaseOrder->date,
          'purchase_request'=>$purchaseOrder->purchase_request,
          'purchase_order_details'=>$purchase_order_details,
          // 'purchase_order_details'=>function($q){
          //   $q->with('material');
          // }
        ]);

      }


      return response()->json([
        "data"=>$data,
      ],200);


    }



    public function getQtyInfoReturn(Request $request)
    {

      $rules = [
        'supplier_code' => 'required|exists:App\Model\Supplier,code',
      ];

      $messages=[
        'supplier_code.required' => 'Kode Supplier harus ada',
        'supplier_code.exists' => 'Kode Supplier tidak terdaftar',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $admin_code = $this->admin->code;
      $supplier_code = $request->supplier_code;
      $exclude_rn_no = $request->purchase_return_number;

      $purchaseOrders=PurchaseOrder::where('supplier_code',$supplier_code)->orderBy("number","desc")->with([
        'approver'=>function($q){
          $q->with(['employee']);
        },
        'supplier',
        'purchase_request',
        'purchase_order_details'=>function($q){
          $q->with('material');
        }
      ])->get();

      $data=[];
      foreach ($purchaseOrders as $key => $purchaseOrder) {

        $purchase_order_details=[];

        foreach ($purchaseOrder->purchase_order_details as $key => $purchase_order_detail) {
          array_push($purchase_order_details,[
            "purchase_order_number"=>$purchase_order_detail->purchase_order_number,
            "ordinal"=>$purchase_order_detail->ordinal,
            "material"=>$purchase_order_detail->material,
            "po_qty"=>$purchase_order_detail->qty,
            "gr_qty"=>0,
            "rn_qty"=>0,
          ]);
        }

        $goods_receipt_details = \App\Model\GoodsReceiptDetail::whereIn("goods_receipt_number",function($q)use($purchaseOrder,$exclude_rn_no){
          $q->select('number')->from('goods_receipts');
          $q->where("purchase_order_number",$purchaseOrder->number);
          $q->where("supplier_code",$purchaseOrder->supplier_code);
        })->get();

        foreach ($goods_receipt_details as $key => $goods_receipt_detail) {
          $indexOf = array_search($goods_receipt_detail->material->code,array_map(function($q){
            return $q["material"]["code"];
          },$purchase_order_details));

          $purchase_order_details[$indexOf]["gr_qty"]+=$goods_receipt_detail->qty;
        }

        $purchase_return_details = \App\Model\PurchaseReturnDetail::whereIn("purchase_return_number",function($q)use($purchaseOrder,$exclude_rn_no){
          $q->select('number')->from('purchase_returns');
          $q->where("purchase_order_number",$purchaseOrder->number);
          $q->where("supplier_code",$purchaseOrder->supplier_code);
          if ($exclude_rn_no) {
            $q->where("number","!=",$exclude_rn_no);
          }
        })->get();
        foreach ($purchase_return_details as $key => $purchase_return_detail) {
          $indexOf = array_search($purchase_return_detail->material->code,array_map(function($q){
            return $q["material"]["code"];
          },$purchase_order_details));

          $purchase_order_details[$indexOf]["rn_qty"]+=$purchase_return_detail->qty;
        }

        array_push($data,[
          'number' => $purchaseOrder->number,
          'date' => $purchaseOrder->date,
          'purchase_request'=>$purchaseOrder->purchase_request,
          'purchase_order_details'=>$purchase_order_details,
        ]);

      }


      return response()->json([
        "data"=>$data,
      ],200);


    }

    public function generateProofOfExpenditure(Request $request)
    {


      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      }

      DB::beginTransaction();
      try {
        $front = "POE.".substr(date("Y"),-2)."-N";
        $poe=\App\Model\ProofOfExpenditure::where("number",'like',$front.'%')->orderBy("created_at","desc")->first();
        if ($poe) {
          $number = $front.str_pad((int)substr($poe->number,8)+1, 4, "0", STR_PAD_LEFT);
        }else {
          $number = $front."0001";
        }
        $admin_code = $this->admin->code;

        $data=new \App\Model\ProofOfExpenditure();
        $data->admin_code=$admin_code;
        $data->number=$number;
        $data->date=date("Y-m-d");
        $data->pay_date=date("Y-m-d");
        $data->save();

        if (!$request->proof_of_expenditure_details) {
          throw new \Exception("Silahkan masukkan data detail purchase request");
        }

        $proof_of_expenditure_details = json_decode($request->proof_of_expenditure_details,true);
        if (count($proof_of_expenditure_details)==0) {
          throw new \Exception("Silahkan pilih list terlebih dahulu");
        }
        $arr_purchase_orders= [];
        $ordinal = 0;
        foreach ($proof_of_expenditure_details as $key => $value) {

          $ordinal+=1;

          $rules = [
            'number' => 'required|exists:App\Model\PurchaseOrder,number',
          ];

          $messages=[
            'number.required' => 'No PO harus di pilih',
            'number.exists' => 'No PO tidak terdaftar',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }

          if (in_array($value["number"],$arr_purchase_orders)) {
            throw new \Exception("No PO tidak boleh sama");
          }

          array_push($arr_purchase_orders,$value["number"]);

          $add = new \App\Model\ProofOfExpenditureDetail();
          $add->admin_code = $admin_code;
          $add->ordinal = $ordinal;
          $add->proof_of_expenditure_number = $number;
          $add->description = $value["number"];
          $add->save();

          $edit = PurchaseOrder::where('number',$value["number"])->update([
            'proof_of_expenditure_number'=>$number
          ]);

          foreach (\App\Model\PurchaseReturn::where("purchase_order_number",$value["number"])->get() as $key => $prn) {
            $ordinal+=1;
            $add = new \App\Model\ProofOfExpenditureDetail();
            $add->admin_code = $admin_code;
            $add->ordinal = $ordinal;
            $add->proof_of_expenditure_number = $number;
            $add->description = $prn->number;
            $add->save();
          }


        }

        DB::commit();
        return response()->json([
          "data"=>[
            "number"=>$number
          ],
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage(),400);
      }

    }

    public function locking(Request $request)
    {

      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      }

      $rules = [
         'number' => 'required|regex:/^\S*$/|exists:App\Model\PurchaseOrder,number',
      ];

      $messages=[
        'number.required' => 'PO No harus ada',
        'number.exists' => 'PO No tidak terdaftar',
        'number.regex' => 'PO No tidak boleh ada spasi',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        foreach ($validator->messages()->all() as $k => $v) {
          throw new MyException($v);
        }
      }

      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $number = $request->number;

        $data=PurchaseOrder::where('number',$number)->first();
        $data->admin_code=$admin_code;
        $data->lock_by=$data->lock_by == null ? $admin_code : null;
        $data->save();

        DB::commit();

        return response()->json([
          "data"=>$data->lock_by ?? "",
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage(),400);
      }

    }


    public function cetak(Request $request)
    {

      $number = $request->number;
      $filename = $request->filename ?? "tx-".MyLib::timestamp();
      $data = \App\Model\PurchaseOrder::find($number);
      $datas = $data->purchase_order_details;

      $company = new MyLib();
      $mime=MyLib::mime("pdf");

      $pdf = PDF::loadView('laporan.purchase_order', ["data"=>$data, "datas"=>$datas,"company"=>$company->company])
      ->setPaper('a4', 'landscape');

      // $pdf = PDF::loadView('laporan.material', ["data"=>$employees, "company"=>$company->company])->setPaper('a4', 'portrait')->setOptions(['isPhpEnabled' => true,'isJavascriptEnabled'=>true,'javascriptDelay'=>13500]);
      // $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('laporan.material', ["data"=>$employees, "company"=>$company->company,"b62"=>$base64,"pp"=>$public_path])->setPaper('a4', 'portrait');
      // $pdf->output();
      // $dom_pdf = $pdf->getDomPDF();
      //
      // $canvas = $dom_pdf ->get_canvas();
      // $canvas->page_text(0, 0, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));
      $bs64=base64_encode($pdf->download($filename.'.pdf'));

      $result =[
        "contentType"=>$mime["contentType"],
        "data"=>$bs64,
        "dataBase64"=>$mime["dataBase64"].$bs64,
        "filename"=>$filename
      ];

      return $result;
    }
}
