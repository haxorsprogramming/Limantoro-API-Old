<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\ProofOfExpenditure;

use App\Http\Requests\ProofOfExpenditureReq;
use App\Http\Resources\ProofOfExpenditureResource;

use App\Helpers\MyLib;
use DB;
use PDF;
class ProofOfExpenditures extends Controller
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
      $purchaseRequest = ProofOfExpenditure::offset($offset)->limit($limit);

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

        // if (isset($likeList["name"])) {
        //   $purchaseRequest = $purchaseRequest->orWhere("name","like",$likeList["name"]);
        // }


      }

      return response()->json([
        "data"=>ProofOfExpenditureResource::collection($purchaseRequest->with([
          // 'approver'=>function($q){
          //   $q->with(['employee']);
          // },
          // 'supplier',
          // 'purchase_request',
        ])->get()),
      ],200);
    }

    // public function store(ProofOfExpenditureReq $request)
    // {
    //   if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
    //     throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
    //   }
    //
    //   DB::beginTransaction();
    //   try {
    //     $front = "PO.".substr(date("Y"),-2)."-";
    //     $pr=ProofOfExpenditure::where("number",'like',$front.'%')->orderBy("created_at","desc")->first();
    //     if ($pr) {
    //       $number = $front.str_pad((int)substr($pr->number,6)+1, 4, "0", STR_PAD_LEFT);
    //     }else {
    //       $number = $front."0001";
    //     }
    //     $admin_code = $this->admin->code;
    //     $purchase_request_number=$request->purchase_request_number;
    //
    //     $data=new ProofOfExpenditure();
    //     $data->admin_code=$admin_code;
    //     $data->number=$number;
    //     $data->date=$request->date;
    //     $data->purchase_request_number=$purchase_request_number;
    //     $data->supplier_code=$request->supplier_code;
    //     // $data->proof_of_payment_number=$request->proof_of_payment_number;
    //     // $data->approver_code=$request->approver_code;
    //     $data->save();
    //
    //     $purchase_order_detail=[];
    //     if (!$request->purchase_order_details) {
    //       throw new \Exception("Silahkan masukkan data detail");
    //     }
    //
    //     $purchase_order_details = json_decode($request->purchase_order_details,true);
    //     // if (count($purchase_order_details)==0) {
    //     //   throw new \Exception("Silahkan masukkan data detail purchase request");
    //     // }
    //
    //     $compareItems=$this->compareItems($purchase_request_number);
    //
    //     $materials= [];
    //     foreach ($purchase_order_details as $key => $value) {
    //       $ordinal = $key + 1;
    //
    //       $rules = [
    //         'material_code' => 'required|exists:App\Model\Material,code',
    //         'qty' => 'required|min:1|numeric',
    //         'price' => 'required|numeric',
    //       ];
    //
    //       $messages=[
    //         'material_code.required' => 'Material harus di pilih',
    //         'material_code.exists' => 'Material tidak terdaftar',
    //
    //         'qty.required' => 'Quantity yang diminta tidak boleh kosong',
    //         'qty.min' => 'Quantity yang diminta minimal 1',
    //         'qty.numeric' => 'Quantity yang diminta harus angka',
    //
    //         'price.required' => 'Harga tidak boleh kosong',
    //         'price.numeric' => 'Harga harus angka',
    //       ];
    //
    //       $validator = \Validator::make($value,$rules,$messages);
    //       if ($validator->fails()) {
    //         foreach ($validator->messages()->all() as $k => $v) {
    //           throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
    //         }
    //       }
    //
    //       $material_code = $value["material_code"];
    //
    //       if ($value['qty'] > $compareItems[$material_code] ) {
    //         throw new \Exception("Baris Data Ke-".$ordinal." "."Qty tidak boleh lebih dari ".$compareItems[$material_code]);
    //       }
    //
    //       if (in_array($material_code,$materials)) {
    //         throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan tidak boleh sama");
    //       }
    //
    //       array_push($materials,$material_code);
    //
    //       $purchase_order_detail = new \App\Model\ProofOfExpenditureDetail();
    //       $purchase_order_detail->admin_code = $admin_code;
    //       $purchase_order_detail->purchase_order_number = $number;
    //       $purchase_order_detail->ordinal = $ordinal;
    //       $purchase_order_detail->material_code = $value['material_code'];
    //       $purchase_order_detail->qty = $value['qty'];
    //       $purchase_order_detail->price = $value['price'];
    //       $purchase_order_detail->note = $value['note'];
    //       $purchase_order_detail->save();
    //     }
    //
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
    // }

    public function update(ProofOfExpenditureReq $request)
    {

      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menugbah Data");
      }




      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $number = $request->number;
        $payby = $request->payby;

        $newNumber = preg_replace('/[KBN]/',$payby,$number);

        $data=ProofOfExpenditure::where('number',$number)->first();
        $data->number= $newNumber;
        $data->admin_code=$admin_code;
        $data->date=$request->date;
        $data->is_paid=$request->is_paid;
        $data->pay_date=$request->pay_date;
        $data->note=$request->note;
        $data->bank_1=$request->bank_1;
        $data->check_number_1=$request->check_number_1;
        $data->total_1=$request->total_1;
        $data->bank_2=$request->bank_2;
        $data->check_number_2=$request->check_number_2;
        $data->total_2=$request->total_2;
        $data->discount=$request->discount;
        $data->save();

        $proof_of_expenditure_details=[];
        if (!$request->proof_of_expenditure_details) {
          throw new \Exception("Silahkan masukkan data detail purchase request");
        }
        $proof_of_expenditure_details = json_decode($request->proof_of_expenditure_details,true);

        // if (\App\Model\ProofOfExpenditureDetail::where("approved_qty",">",0)->where("purchase_order_number",$number)->first()) {
        //   throw new \Exception("Maaf purchase request sudah di approved , data sudah tidak dapat di ubah");
        // }

        // $compareItems=$this->compareItems($purchase_request_number,$number);
        $purchase_order_numbers=[];
        $total=0;
        $ordinal=0;
        foreach ($proof_of_expenditure_details as $key => $value) {
          if (explode(".",$value["description"])[0]!="PO") {
            continue;
          }
          $ordinal += 1;

          $rules = [
            'description' => 'required',
          ];

          $messages=[
            'description.required' => 'Uraian harus di isi',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }

          $po_detect = \App\Model\PurchaseOrder::where("number",$value["description"])->first();
          if ($po_detect->proof_of_expenditure_number!=null && $po_detect->proof_of_expenditure_number!=$newNumber) {
            throw new \Exception("Maaf ".$value["description"]." sudah digunakan");
          }

          if (in_array($value["description"],$purchase_order_numbers)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Uraian yang dimasukkan sudah terdaftar");
          }

          array_push($purchase_order_numbers,$value["description"]);

          $materials=[];

          foreach ($po_detect->purchase_order_details as $key => $pod) {
            if (!isset($materials[$pod->material_code])) {
              $materials[$pod->material_code]=[];
              $materials[$pod->material_code]["qty"]=$pod->qty;
              $materials[$pod->material_code]["price"]=$pod->price;
            }
          }

          $prnds=\App\Model\PurchaseReturnDetail::whereIn("purchase_return_number",function($q)use($value){
            $q->select("number")->from("purchase_returns")->where("purchase_order_number",$value["description"]);
          })->get();

          foreach ($prnds as $prnd) {
            if (isset($materials[$prnd->material_code])) {
              $materials[$prnd->material_code]["qty"]-=$prnd->qty;
            }
          }

          $total += array_reduce($materials,function($c,$i){
            $c+= ($i["qty"]*$i["price"]);
            return $c;
          });
        }

        if ($total - $request->discount < 0) {
          throw new \Exception("Maaf total tagihan tidak sesuai mohon di periksa kembali");
        }

        if ($request->total_1 || $request->total_2) {
          $discount = $request->discount ?? 0;
          $total_1 = $request->total_1 ?? 0;
          $total_2 = $request->total_2 ?? 0;

          if ($total_1+$total_2 != $total - $request->discount) {
            throw new \Exception("Total Bayar tidak sesuai dengan total tagihan.. mohon di cek kembali");
          }
        }




        $purchase_order_numbers=[];
        \App\Model\ProofOfExpenditureDetail::where("proof_of_expenditure_number",$newNumber)->delete();

        $ordinal=0;
        foreach ($proof_of_expenditure_details as $key => $value) {
          if (explode(".",$value["description"])[0]!="PO") {
            continue;
          }
          $ordinal += 1;

          $rules = [
            'description' => 'required',
          ];

          $messages=[
            'description.required' => 'Uraian harus di isi',
          ];

          $po_detect = \App\Model\PurchaseOrder::where("number",$value["description"])->first();
          if ($po_detect->proof_of_expenditure_number!=null && $po_detect->proof_of_expenditure_number!=$newNumber) {
            throw new \Exception("Maaf ".$value["description"]." sudah digunakan");
          }

          \App\Model\PurchaseOrder::where("number",$value["description"])->where("proof_of_expenditure_number",$newNumber)->update([
            "proof_of_expenditure_number"=>null
          ]);

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }


          if (in_array($value["description"],$purchase_order_numbers)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Uraian yang dimasukkan sudah terdaftar");
          }

          array_push($purchase_order_numbers,$value["description"]);

          $poe = new \App\Model\ProofOfExpenditureDetail();
          $poe->admin_code = $admin_code;
          $poe->proof_of_expenditure_number = $newNumber;
          $poe->ordinal = $ordinal;
          $poe->description = $value['description'];
          $poe->save();

          $po = \App\Model\PurchaseOrder::where("number",$value['description'])->whereNull('proof_of_expenditure_number')->first();
          if (!$po) {
            throw new \Exception("Data tidak ditemukan");
          }
          $po->admin_code = $admin_code;
          $po->proof_of_expenditure_number = $newNumber;
          $po->save();

          foreach (\App\Model\PurchaseReturn::where("purchase_order_number",$value["description"])->get() as $key => $prn) {
            $ordinal+=1;
            $add = new \App\Model\ProofOfExpenditureDetail();
            $add->admin_code = $admin_code;
            $add->ordinal = $ordinal;
            $add->proof_of_expenditure_number = $newNumber;
            $add->description = $prn->number;
            $add->save();
          }

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

      $data = $this->recreateData($request);

      // $data = ProofOfExpenditure::where("number",$request->number)->with([
      //   'proof_of_expenditure_details'=>function($q){
      //     $q->orderBy("ordinal","asc");
      //   }
      // ])->first();
      //
      // $data =json_decode(json_encode(new ProofOfExpenditureResource($data)),true);
      //
      // $proof_of_expenditure_details = $data["proof_of_expenditure_details"];
      //
      // foreach ($proof_of_expenditure_details as $key => $proof_of_expenditure_detail) {
      //   $desc = $proof_of_expenditure_detail["description"];
      //
      //   if (explode(".",$desc)[0]=='PO') {
      //
      //     $materials=[];
      //
      //     $purchase_orders = \App\Model\PurchaseOrderDetail::where("purchase_order_number",$desc)->get();
      //     foreach ($purchase_orders as $po) {
      //       if (!isset($materials[$po->material_code])) {
      //         $materials[$po->material_code]=[];
      //         $materials[$po->material_code]["qty"]=$po->qty;
      //         $materials[$po->material_code]["price"]=$po->price;
      //       }
      //     }
      //
      //     $data["proof_of_expenditure_details"][$key]["total"] = array_reduce($materials,function($c,$i){
      //       $c+= ($i["qty"]*$i["price"]);
      //       return $c;
      //     });
      //
      //   }
      //
      //   if (explode(".",$desc)[0]=='PRN') {
      //
      //     $materials=[];
      //
      //     $purchase_return = \App\Model\PurchaseReturn::where("number",$desc)->first();
      //
      //     $purchase_orders = \App\Model\PurchaseOrderDetail::where("purchase_order_number",$purchase_return->purchase_order_number)->get();
      //     foreach ($purchase_orders as $po) {
      //       if (!isset($materials[$po->material_code])) {
      //         $materials[$po->material_code]=[];
      //         $materials[$po->material_code]["qty"]=0;
      //         $materials[$po->material_code]["price"]=$po->price;
      //       }
      //     }
      //
      //     foreach ($purchase_return->purchase_return_details as $pr) {
      //       if (isset($materials[$pr->material_code])) {
      //         $materials[$pr->material_code]["qty"]+=$pr->qty;
      //       }
      //     }
      //
      //     $data["proof_of_expenditure_details"][$key]["total"] = array_reduce($materials,function($c,$i){
      //       $c-= ($i["qty"]*$i["price"]);
      //       return $c;
      //     },0);
      //
      //   }
      //
      // }
      //
      //
      // if (!$data) {
      //     throw new MyException("Maaf Data Tidak Ditemukan");
      // }

      return response()->json([
        "data"=>$data,
      ],200);
    }


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
        $purchase_order_details=\App\Model\ProofOfExpenditureDetail::whereIn("purchase_order_number",
        function($q)use($purchase_request_number){
          $q->select("number");
          $q->from('purchase_orders');
          $q->where('purchase_request_number',$purchase_request_number);
        })->get();
      }else {
        $purchase_order_details=\App\Model\ProofOfExpenditureDetail::whereIn("purchase_order_number",
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

    public function getDescriptions(Request $request)
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
      $purchaseOrder = \App\Model\PurchaseOrder::offset($offset)->limit($limit);

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
        //   $purchaseOrder = $purchaseOrder->orderBy("created_at",$sortList["created_at"]);
        // }
        //
        // if (isset($sortList["updated_at"])) {
        //   $purchaseOrder = $purchaseOrder->orderBy("updated_at",$sortList["updated_at"]);
        // }

        // if (isset($sortList["role"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $purchaseOrder = $purchaseOrder->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $purchaseOrder = $purchaseOrder->orderBy('number','DESC');
      }

      // ==============
      // Model Filter
      // ==============

      // if (isset($request->created_by_name)) {
      //   $purchaseOrder = $purchaseOrder->where('created_by',function($q)use($request){
      //     $q->select('id');
      //     $q->from('users');
      //     $q->where('username','like','%'.$request->created_by_name.'%');
      //   });
      // }
      // if (isset($request->code)) {
      //   $purchaseOrder = $purchaseOrder->whereRaw('CONCAT("PR-",LPAD(`id`,10,"0")) like ?',['%'.$request->code.'%']);
      // }

      // if (isset($request->status)) {
      //   $purchaseOrder = $purchaseOrder->where("status",'like','%'.$request->status.'%');
      // }
      // if (isset($request->admin_id)) {
      //   $purchaseOrder = $purchaseOrder->where("admin_id",'like','%'.$request->admin_id.'%');
      // }
      //
      // if (in_array($this->admin->role->title,["Manager Lapangan"])) {
      //   $purchaseOrder = $purchaseOrder->where('created_by',$this->admin->id);
      // }
      //
      // if (in_array($this->admin->role->title,["Owner"])) {
      //   $purchaseOrder = $purchaseOrder->where('status','diajukan');
      // }

      // if (isset($request->status) && $request->status!=="") {
      //   $datas = $datas->where('status',$request->status);
      // }

      // $datas=$datas->select('*')->selectRaw('CONCAT("PO-",LPAD(`id`,10,"0")) as `code`');

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
      // $purchaseOrder=$purchaseOrder->get();

      // $purchaseOrders=$purchaseOrder->with([
      //   'purchase_order_details',
      // ])->whereNull('proof_of_expenditure_number')->get();

      $purchaseOrders=$purchaseOrder->with([
        'purchase_order_details',
      ])->get();


      $data=[];
      foreach ($purchaseOrders as $key => $purchaseOrder) {
        $total = 0;

        foreach ($purchaseOrder->purchase_order_details as $k => $purchase_order_detail) {
          $total += $purchase_order_detail->qty * $purchase_order_detail->price;
        }

        array_push($data,[
          "description"=>$purchaseOrder->number,
          "total"=>$total,
          "parent"=>""
        ]);
      }
      return response()->json([
        "data"=>$data,
      ],200);
    }
    public function getDescriptionReturn(Request $request)
    {
      $purchase_order = \App\Model\PurchaseOrder::where("number",$request->number)->first();
      $material_price=[];
      foreach ($purchase_order->purchase_order_details as $key => $purchase_order_detail) {
        $material_price[$purchase_order_detail->material_code]=$purchase_order_detail->price;
      }

      $data =[];
      $purchase_returns = \App\Model\PurchaseReturn::where('purchase_order_number',$request->number)->get();
      foreach ($purchase_returns as $key => $purchase_return) {
        $total = 0;
        foreach ($purchase_return->purchase_return_details as $pr) {
          $total+=$pr->qty * $material_price[$pr->material_code];
        }
        array_push($data,[
          "description"=>$purchase_return->number,
          "total"=>$total*-1,
          "parent"=>$request->number
        ]);
      }

      return response()->json([
        "data"=>$data,
      ],200);
    }
    public function cetak(Request $request)
    {
      $data["total"]=0;

      $number = $request->number;
      $filename = $request->filename ?? "tx-".MyLib::timestamp();
      // $data = \App\Model\ProofOfExpenditure::find($number);
      // $datas = $data->proof_of_expenditure_details;
      $data = $this->recreateData($request);
      $data["total"] = array_reduce($data['proof_of_expenditure_details'],function($c,$i){
          $c+=$i["total"];
          return $c;
      },0);

      $data["total_1"] = !$data["total_1"] ? 0 : $data["total_1"];
      $data["total_2"] = !$data["total_2"] ? 0 : $data["total_2"];

      $company = new MyLib();
      $mime=MyLib::mime("pdf");


      $owner = \App\Model\User::where("role_id",1)->first();
      $pdf = PDF::loadView('laporan.proof_of_expenditure', ["data"=>$data, "owner"=>$owner,"company"=>$company->company])
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



    public function recreateData($request)
    {
      $data = \App\Model\ProofOfExpenditure::where("number",$request->number)->with([
        'admin',
        'proof_of_expenditure_details'=>function($q){
          $q->orderBy("ordinal","asc");
        }
      ])->first();

      $data =json_decode(json_encode(new ProofOfExpenditureResource($data)),true);

      $proof_of_expenditure_details = $data["proof_of_expenditure_details"];

      foreach ($proof_of_expenditure_details as $key => $proof_of_expenditure_detail) {
        $desc = $proof_of_expenditure_detail["description"];

        if (explode(".",$desc)[0]=='PO') {

          $materials=[];

          $purchase_orders = \App\Model\PurchaseOrderDetail::where("purchase_order_number",$desc)->get();
          foreach ($purchase_orders as $po) {
            if (!isset($materials[$po->material_code])) {
              $materials[$po->material_code]=[];
              $materials[$po->material_code]["qty"]=$po->qty;
              $materials[$po->material_code]["price"]=$po->price;
            }
          }

          $data["proof_of_expenditure_details"][$key]["total"] = array_reduce($materials,function($c,$i){
            $c+= ($i["qty"]*$i["price"]);
            return $c;
          });


          $data["proof_of_expenditure_details"][$key]["parent"] = "";

        }

        if (explode(".",$desc)[0]=='PRN') {

          $materials=[];

          $purchase_return = \App\Model\PurchaseReturn::where("number",$desc)->first();

          $purchase_orders = \App\Model\PurchaseOrderDetail::where("purchase_order_number",$purchase_return->purchase_order_number)->get();
          foreach ($purchase_orders as $po) {
            if (!isset($materials[$po->material_code])) {
              $materials[$po->material_code]=[];
              $materials[$po->material_code]["qty"]=0;
              $materials[$po->material_code]["price"]=$po->price;
            }
          }

          foreach ($purchase_return->purchase_return_details as $pr) {
            if (isset($materials[$pr->material_code])) {
              $materials[$pr->material_code]["qty"]+=$pr->qty;
            }
          }

          $data["proof_of_expenditure_details"][$key]["total"] = array_reduce($materials,function($c,$i){
            $c-= ($i["qty"]*$i["price"]);
            return $c;
          },0);

          $data["proof_of_expenditure_details"][$key]["parent"] = $purchase_return->purchase_order_number;

        }
      }

      if (!$data) {
          throw new MyException("Maaf Data Tidak Ditemukan");
      }
      return $data;
    }
}
