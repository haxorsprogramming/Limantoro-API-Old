<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\GoodsReceipt;

use App\Http\Requests\GoodsReceiptReq;
use App\Http\Resources\GoodsReceiptResource;

use App\Helpers\MyLib;
use DB;
use PDF;
class GoodsReceipts extends Controller
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
      $getData = GoodsReceipt::offset($offset)->limit($limit);

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
        //   $getData = $getData->orderBy("created_at",$sortList["created_at"]);
        // }
        //
        // if (isset($sortList["updated_at"])) {
        //   $getData = $getData->orderBy("updated_at",$sortList["updated_at"]);
        // }

        // if (isset($sortList["role"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $getData = $getData->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $getData = $getData->orderBy('number','DESC');
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
          $getData = $getData->where("number","like",$likeList["number"]);
        }

        if (isset($likeList["purchase_order_number"])) {
          $getData = $getData->orWhere("purchase_order_number","like",$likeList["purchase_order_number"]);
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
      // $getData=$getData->get();


      return response()->json([
        "data"=>GoodsReceiptResource::collection($getData->with([
          'supplier',
          'purchase_order',
          'checker',
        ])->get()),
      ],200);
    }

    public function store(GoodsReceiptReq $request)
    {
      if (!in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      }

      $po=\App\Model\PurchaseOrder::where("number",$request->purchase_order_number)->whereNotNull("lock_by")->first();
      if ($po) { throw new MyException("Maaf No PO sudah dikunci"); }

      DB::beginTransaction();
      try {
        $front = "GR.".substr(date("Y"),-2)."-";
        $pr=GoodsReceipt::where("number",'like',$front.'%')->orderBy("created_at","desc")->first();
        if ($pr) {
          $number = $front.str_pad((int)substr($pr->number,6)+1, 4, "0", STR_PAD_LEFT);
        }else {
          $number = $front."0001";
        }
        $admin_code = $this->admin->code;
        $purchase_order_number=$request->purchase_order_number;

        $data=new GoodsReceipt();
        $data->admin_code=$admin_code;
        $data->number=$number;
        $data->date=$request->date;
        $data->delivery_order_letter_number=$request->delivery_order_letter_number;
        $data->purchase_order_number=$purchase_order_number;
        $data->supplier_code=$request->supplier_code;
        // $data->proof_of_payment_number=$request->proof_of_payment_number;
        // $data->approver_code=$request->approver_code;
        $data->save();

        $goods_receipt_detail=[];
        if (!$request->goods_receipt_details) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $goods_receipt_details = json_decode($request->goods_receipt_details,true);
        if (count($goods_receipt_details)==0) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $compareItems=$this->compareItems($purchase_order_number);

        $materials= [];
        $materials_qty= [];
        foreach ($goods_receipt_details as $key => $value) {
          $ordinal = $key + 1;
          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            'qty' => 'required|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            'qty.required' => 'Quantity yang diminta tidak boleh kosong',
            'qty.numeric' => 'Quantity yang diminta harus angka',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }

          $material_code = $value["material_code"];
          $available_qty = $compareItems[$material_code]["po_qty"] - $compareItems[$material_code]["gr_qty"] + $compareItems[$material_code]["rn_qty"];

          if ($value['qty'] > $available_qty ) {
            throw new \Exception("Baris Data Ke-".$ordinal." "."Qty tidak boleh lebih dari ".$available_qty);
          }

          if (in_array($material_code,$materials)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan tidak boleh sama");
          }

          array_push($materials,$material_code);
          array_push($materials_qty,$value['qty']);

          $goods_receipt_detail = new \App\Model\GoodsReceiptDetail();
          $goods_receipt_detail->admin_code = $admin_code;
          $goods_receipt_detail->goods_receipt_number = $number;
          $goods_receipt_detail->ordinal = $ordinal;
          $goods_receipt_detail->material_code = $value['material_code'];
          $goods_receipt_detail->qty = $value['qty'];
          $goods_receipt_detail->save();

          if (count($goods_receipt_details)-1==$key && max($materials_qty)==0) {
            throw new \Exception("Qty yang dimasukkan semua 0. Data Tidak Dapat Di proses");
          }
        }


        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage());
      }

    }

    public function update(GoodsReceiptReq $request)
    {

      if (!in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Mengubah Data");
      }

      $po=\App\Model\PurchaseOrder::where("number",$request->purchase_order_number)->whereNotNull("lock_by")->first();
      if ($po) { throw new MyException("Maaf No PO sudah dikunci"); }

      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $purchase_order_number = $request->purchase_order_number;
        $number = $request->number;

        $data=GoodsReceipt::where('number',$number)->first();

        if ($data->checker_code) {
          throw new \Exception("GR sudah disetujui, tidak dapat diubah lagi");
        }

        $data->admin_code=$admin_code;
        $data->date=$request->date;
        $data->delivery_order_letter_number=$request->delivery_order_letter_number;
        $data->purchase_order_number=$purchase_order_number;
        $data->supplier_code=$request->supplier_code;
        // $data->proof_of_payment_number=$request->proof_of_payment_number;
        // $data->approver_code=$request->approver_code;
        $data->save();

        $goods_receipt_detail=[];
        if (!$request->goods_receipt_details) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $goods_receipt_details = json_decode($request->goods_receipt_details,true);
        if (count($goods_receipt_details)==0) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        // if (\App\Model\GoodsReceiptDetail::where("approved_qty",">",0)->where("goods_receipt_number",$number)->first()) {
        //   throw new \Exception("Maaf purchase request sudah di approved , data sudah tidak dapat di ubah");
        // }

        $compareItems=$this->compareItems($purchase_order_number,$number);

        $materials=[];
        $materials_qty=[];
        \App\Model\GoodsReceiptDetail::where("goods_receipt_number",$number)->delete();

        foreach ($goods_receipt_details as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            'qty' => 'required|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            'qty.required' => 'Quantity yang diminta tidak boleh kosong',
            'qty.numeric' => 'Quantity yang diminta harus angka',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }


          $material_code = $value["material_code"];

          $available_qty = $compareItems[$material_code]["po_qty"] - $compareItems[$material_code]["gr_qty"] + $compareItems[$material_code]["rn_qty"];

          if ($value['qty'] > $available_qty ) {
            throw new \Exception("Baris Data Ke-".$ordinal." "."Qty tidak boleh lebih dari ".$available_qty);
          }

          if (in_array($material_code,$materials)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan sudah terdaftar");
          }
          array_push($materials,$material_code);
          array_push($materials_qty,$value['qty']);

          if (count($goods_receipt_details)-1==$key && max($materials_qty)==0) {
            throw new \Exception("Qty yang dimasukkan semua 0. Data Tidak Dapat Di proses");
          }

          $goods_receipt_detail = new \App\Model\GoodsReceiptDetail();
          $goods_receipt_detail->admin_code = $admin_code;
          $goods_receipt_detail->goods_receipt_number = $number;
          $goods_receipt_detail->ordinal = $ordinal;
          $goods_receipt_detail->material_code = $value['material_code'];
          $goods_receipt_detail->qty = $value['qty'];
          $goods_receipt_detail->save();

        }

        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage());
      }

    }

    public function show(Request $request)
    {

      $goods_receipt = GoodsReceipt::where("number",$request->number)->with([
        'supplier',
        'purchase_order',
        'checker',
        'goods_receipt_details'=>function($q){
          $q->orderBy("ordinal","asc");
          $q->with(['material']);
        }
      ])->first()->toArray();

      if (!$goods_receipt) {
          throw new MyException("Maaf Data Tidak Ditemukan");
      }

      $purchase_order_details = \App\Model\PurchaseOrderDetail::where('purchase_order_number',$goods_receipt['purchase_order_number'])->get();
      foreach ($purchase_order_details as $key => $purchase_order_detail) {
        $indexOf = array_search($purchase_order_detail->material->code,array_map(function($x){ return $x["material"]["code"];},$goods_receipt["goods_receipt_details"]));
        if ($indexOf>-1) {
          $goods_receipt["goods_receipt_details"][$indexOf]["material"]["po_qty"]=$purchase_order_detail->qty;
          $goods_receipt["goods_receipt_details"][$indexOf]["material"]["gr_qty"]=0;
          $goods_receipt["goods_receipt_details"][$indexOf]["material"]["rn_qty"]=0;
        }
      }

      $get_goods_receipts = \App\Model\GoodsReceiptDetail::whereIn("goods_receipt_number",function($q)use($goods_receipt){
        $q->select('number')->from('goods_receipts')->where("purchase_order_number",$goods_receipt['purchase_order_number'])->where("supplier_code",$goods_receipt['supplier_code'])->where("number","!=",$goods_receipt['number']);
      })->get();

      foreach ($get_goods_receipts as $key => $get_goods_receipt) {
        $indexOf = array_search($get_goods_receipt->material->code,array_map(function($x){ return $x["material"]["code"];},$goods_receipt["goods_receipt_details"]));
        if ($indexOf>-1) {
          $goods_receipt["goods_receipt_details"][$indexOf]["material"]["gr_qty"]+=$get_goods_receipt->qty;
        }
      }

      $get_purchase_returns = \App\Model\PurchaseReturnDetail::whereIn("purchase_return_number",function($q)use($goods_receipt){
        $q->select('number')->from('purchase_returns')->where("purchase_order_number",$goods_receipt['purchase_order_number'])->where("supplier_code",$goods_receipt['supplier_code']);
      })->get();

      foreach ($get_purchase_returns as $key => $get_purchase_return) {
        $indexOf = array_search($get_purchase_return->material->code,array_map(function($x){ return $x["material"]["code"];},$goods_receipt["goods_receipt_details"]));
        if ($indexOf>-1) {
          $goods_receipt["goods_receipt_details"][$indexOf]["material"]["rn_qty"]+=$get_purchase_return->qty;
        }
      }



      return response()->json([
        // "data"=>new GoodsReceiptResource($goods_receipt),
        "data"=>$goods_receipt,
      ],200);
    }


    public function setCheck(Request $request)
    {

      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menentukan qty yang disetujui");
      }

      $rules = [
        'number' => 'required|exists:App\Model\GoodsReceipt,number',
        'is_checked' => [
          Rule::in(['0', '1']),
        ],
      ];

      $messages=[
        'number.required' => 'PR No harus ada',
        'number.exists' => 'PR No tidak terdaftar',
        'is_checked.in' => 'Sudah Diperiksa harus di pilih',

      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $number = $request->number;
        $is_checked = $request->is_checked;

        $data=GoodsReceipt::where('number',$number)->first();
        $data->checker_code=($is_checked==0)?null:$admin_code;
        $data->admin_code=$admin_code;
        $data->save();

        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage());
      }


    }

    public function compareItems($purchase_order_number,$goods_receipt_number=null)
    {
      $compareItems=[];

      $purchase_order=\App\Model\PurchaseOrder::where("number",$purchase_order_number)->first();
      foreach ($purchase_order->purchase_order_details as $key => $purchase_order_detail) {
        if (!array_key_exists($purchase_order_detail->material_code,$compareItems)) {
          $compareItems[$purchase_order_detail->material_code]=[];
          $compareItems[$purchase_order_detail->material_code]["po_qty"]=$purchase_order_detail->qty;
          $compareItems[$purchase_order_detail->material_code]["rn_qty"]=0;
          $compareItems[$purchase_order_detail->material_code]["gr_qty"]=0;
        }
      }

      if ($goods_receipt_number==null) {
        $goods_receipt_details=\App\Model\GoodsReceiptDetail::whereIn("goods_receipt_number",
        function($q)use($purchase_order_number){
          $q->select("number");
          $q->from('goods_receipts');
          $q->where('purchase_order_number',$purchase_order_number);
        })->get();
      }else {
        $goods_receipt_details=\App\Model\GoodsReceiptDetail::whereIn("goods_receipt_number",
        function($q)use($purchase_order_number,$goods_receipt_number){
          $q->select("number");
          $q->from('goods_receipts');
          $q->where('number',"!=",$goods_receipt_number);
          $q->where('purchase_order_number',$purchase_order_number);
        })->get();
      }

      foreach ($goods_receipt_details as $key => $goods_receipt_detail) {
        $compareItems[$goods_receipt_detail->material_code]["gr_qty"]+=$goods_receipt_detail->qty;
      }

      $purchase_return_details=\App\Model\PurchaseReturnDetail::whereIn("purchase_return_number",
      function($q)use($purchase_order_number){
        $q->select("number");
        $q->from('purchase_returns');
        $q->where('purchase_order_number',$purchase_order_number);
      })->get();

      foreach ($purchase_return_details as $key => $purchase_return_detail) {
        $compareItems[$purchase_return_detail->material_code]["rn_qty"]+=$purchase_return_detail->qty;
      }


      return $compareItems;

    }

    public function cetak(Request $request)
    {

      $number = $request->number;
      $filename = $request->filename ?? "tx-".MyLib::timestamp();
      $data = \App\Model\GoodsReceipt::find($number);
      $datas = $data->goods_receipt_details;

      $company = new MyLib();
      $mime=MyLib::mime("pdf");

      $pdf = PDF::loadView('laporan.goods_receipt', ["data"=>$data, "datas"=>$datas,"company"=>$company->company])
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
