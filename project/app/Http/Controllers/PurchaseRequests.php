<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\PurchaseRequest;

use App\Http\Requests\PurchaseRequestReq;
use App\Http\Resources\PurchaseRequestResource;

use App\Helpers\MyLib;
use DB;
use PDF;

class PurchaseRequests extends Controller
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
      $purchaseRequest = PurchaseRequest::offset($offset)->limit($limit);

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

        if (isset($likeList["project_code"])) {
          $purchaseRequest = $purchaseRequest->orWhere("project_code","like",$likeList["project_code"]);
        }


      }

      if ($request->get_data_for_make_po) {

        $purchaseRequest = $purchaseRequest->whereHas("purchase_request_details",function($q){
          $q->where("approved_qty",">",0);
        });

        // mgkn perlu di bandingan dengan PO yang sudah dibuat
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
        "data"=>PurchaseRequestResource::collection($purchaseRequest->with([
          'requester'=>function($q){
            $q->with(['employee']);
          },
          'project'
        ])->get()),
      ],200);
    }

    public function store(PurchaseRequestReq $request)
    {
      if (!in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      }

      DB::beginTransaction();
      try {
        $front = "PR.".substr(date("Y"),-2)."-";
        $pr=PurchaseRequest::where("number",'like',$front.'%')->orderBy("created_at","desc")->first();
        if ($pr) {
          $number = $front.str_pad((int)substr($pr->number,6)+1, 4, "0", STR_PAD_LEFT);
        }else {
          $number = $front."0001";
        }
        $admin_code = $this->admin->code;
        $project_code = $request->project_code;

        $project = \App\Model\Project::where("code",$project_code)->first();
        $in_charge_code = $project->in_charge_code;

        $data=new PurchaseRequest();
        $data->admin_code=$admin_code;
        $data->number=$number;
        $data->date=$request->date;
        $data->requester_code=$in_charge_code;
        $data->project_code=$project_code;
        $data->save();

        if (!$request->purchase_request_details) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $purchase_request_details = json_decode($request->purchase_request_details,true);
        if (count($purchase_request_details) == 0) {
          throw new \Exception("Silahkan masukkan data detail");
        }
        $materials= [];
        foreach ($purchase_request_details as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            // 'note' => 'required|min:3',
            'requested_qty' => 'required|min:1|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            // 'note.required' => 'Note tidak boleh kosong',
            // 'note.min' => 'Note minimal 3 karakter',

            'requested_qty.required' => 'Quantity yang diminta tidak boleh kosong',
            'requested_qty.min' => 'Quantity yang diminta minimal 1',
            'requested_qty.numeric' => 'Quantity yang diminta harus angka',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }

          $material_code = $value["material_code"];
          if (in_array($material_code,$materials)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan tidak boleh sama");
          }

          array_push($materials,$material_code);

          $purchase_request_detail = new \App\Model\PurchaseRequestDetail();
          $purchase_request_detail->admin_code = $admin_code;
          $purchase_request_detail->purchase_request_number = $number;
          $purchase_request_detail->ordinal = $ordinal;
          $purchase_request_detail->material_code = $value['material_code'];
          $purchase_request_detail->note = $value['note'];
          $purchase_request_detail->requested_qty = $value['requested_qty'];
          $purchase_request_detail->approved_qty = 0;
          $purchase_request_detail->save();
        }

        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage(),400);
      }
      // $project_belum_selesai = \App\Model\Project::where("id",$request->project_id)->where("is_finished",false)->first();
      // if (!$project_belum_selesai) {
      //   throw new MyException("Maaf project sudah selesai tidak dapat di gunakan lagi");
      // }
      //
      // $data=new PurchaseRequest($request->except(['id','_token']));
      // $data->admin_id=$this->admin->id;
      // $data->created_by=$this->admin->id;
      // $data->status="dibuat";
      // $data->note="";
      //
      // if ($data->save()) {
      //   return response()->json([
      //     "message"=>"Data berhasil di tambahkan",
      //     "data"=>new PurchaseRequestResource($data)
      //   ],200);
      // }
    }

    public function update(PurchaseRequestReq $request)
    {

      if (!in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Mengubah Data");
      }

      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $project_code = $request->project_code;
        $number = $request->number;

        $project = \App\Model\Project::where("code",$project_code)->first();
        $in_charge_code = $project->in_charge_code;

        $data=PurchaseRequest::where('number',$number)->first();
        $data->admin_code=$admin_code;
        $data->date=$request->date;
        $data->requester_code=$project->in_charge_code;
        $data->project_code=$project_code;
        $data->save();

        if (\App\Model\PurchaseRequestDetail::where("approved_qty",">",0)->where("purchase_request_number",$number)->first()) {
          throw new \Exception("Maaf purchase request sudah di approved , data sudah tidak dapat di ubah");
        }

        if (!$request->purchase_request_details) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $purchase_request_details = json_decode($request->purchase_request_details,true);
        if (count($purchase_request_details) == 0) {
          throw new \Exception("Silahkan masukkan data detail");
        }
        $materials=[];
        \App\Model\PurchaseRequestDetail::where("purchase_request_number",$number)->delete();
        foreach ($purchase_request_details as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            // 'note' => 'required|min:3',
            'requested_qty' => 'required|min:1|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            // 'note.required' => 'Note tidak boleh kosong',
            // 'note.min' => 'Note minimal 3 karakter',

            'requested_qty.required' => 'Quantity yang diminta tidak boleh kosong',
            'requested_qty.min' => 'Quantity yang diminta minimal 1',
            'requested_qty.numeric' => 'Quantity yang diminta harus angka',

          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }


          $material_code = $value["material_code"];
          if (in_array($material_code,$materials)) {
            throw new \Exception("Baris Data Ke-".$ordinal." Material yang dimasukkan sudah terdaftar");
          }
          array_push($materials,$material_code);

          $purchase_request_detail = new \App\Model\PurchaseRequestDetail();
          $purchase_request_detail->admin_code = $admin_code;
          $purchase_request_detail->purchase_request_number = $number;
          $purchase_request_detail->ordinal = $ordinal;
          $purchase_request_detail->material_code = $value['material_code'];
          $purchase_request_detail->note = $value['note'];
          $purchase_request_detail->requested_qty = $value['requested_qty'];
          $purchase_request_detail->approved_qty = 0;
          $purchase_request_detail->save();

        }

        DB::commit();

        return response()->json([
          "message"=>"done"
        ],200);

      } catch (\Exception $e) {
        DB::rollback();
        throw new MyException($e->getMessage(),400);
      }

      //
      // if (!in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
      //   throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      // }
      //
      // $id = $request->id;
      // if ($id=="") {
      //   throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      // }
      //
      // $project_belum_selesai = \App\Model\Project::where("id",$request->project_id)->where("is_finished",false)->first();
      // if (!$project_belum_selesai) {
      //   throw new MyException("Maaf project sudah selesai tidak dapat di gunakan lagi");
      // }
      // if ($project_belum_selesai->in_charge_id!==$this->admin->id) {
      //   throw new MyException("Maaf anda bukan penanggung jawab project ini");
      // }
      //
      // $data = PurchaseRequest::where("id",$id)->where("created_by",$this->admin->id)->first();
      // if (!$data) {
      //   throw new MyException("Maaf Data Tidak Ditemukan");
      // }
      // if ($data->status!=="dibuat") {
      //   throw new MyException("Maaf Data Ini Sudah Tidak Dapat Diubah Lagi");
      // }
      // $data->admin_id=$this->admin->id;
      // $data->project_id=$request->project_id;
      //
      // if ($data->save()) {
      //   return response()->json([
      //     "message"=>"Data berhasil di ubah",
      //     "data"=>new PurchaseRequestResource($data)
      //   ],200);
      // }
    }

    public function show(Request $request)
    {

      $rules = [
        'number' => 'required|exists:App\Model\PurchaseRequest,number',
      ];

      $messages=[
        'number.required' => 'PR No harus ada',
        'number.exists' => 'PR No tidak terdaftar',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $data = new PurchaseRequest();

      $data=$data->where("number",$request->number);

      $data=$data->with([
        'requester'=>function($q){
          $q->with(['employee']);
        },
        'project',
        'purchase_request_details'=>function($q){
          $q->orderBy("ordinal","asc");
          $q->with(['material']);
        }
      ])->first();

      if (!$data) {
          throw new MyException("Maaf Data Tidak Ditemukan");
      }

      return response()->json([
        "data"=>new PurchaseRequestResource($data),
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
    //   $status=$request->status;
    //
    //   if ($status == 'diajukan' && !in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
    //     throw new MyException("Maaf anda tidak punya izin untuk pengajuan pemesanan pembelian");
    //   }
    //
    //   if (in_array($status,["diterima","ditolak"]) && !in_array($this->admin->role->title,["Developer","Owner"])) {
    //     throw new MyException("Maaf anda tidak punya izin untuk menerima atau pun menolak pemesanan pembelian ");
    //   }
    //
    //   $data = PurchaseRequest::where("id",$id)->first();
    //   if (!$data) {
    //     throw new MyException("Harap refresh browser anda");
    //   }
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
    //       "data"=>new PurchaseRequestResource($data)
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
    //   $data = PurchaseRequest::where("id",$id)->first();
    //   $data->admin_id=$this->admin->id;
    //   $data->name=$request->name ?? $data->name;
    //   $data->address=$request->address ?? $data->address;
    //   $data->phone_number=$request->phone_number ?? $data->phone_number;
    //
    //   if ($data->save()) {
    //     return response()->json([
    //       "message"=>"Data berhasil di ubah",
    //       "data"=>new PurchaseRequestResource($data)
    //     ],200);
    //   }
    // }


    public function setApprovedQty(Request $request)
    {

      if (!in_array($this->admin->role->title,["Owner","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menentukan qty yang disetujui");
      }

      $rules = [
        'number' => 'required|exists:App\Model\PurchaseRequest,number',
      ];

      $messages=[
        'number.required' => 'PR No harus ada',
        'number.exists' => 'PR No tidak terdaftar',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $number = $request->number;

        $data=PurchaseRequest::where('number',$number)->first();
        $PO = \App\Model\PurchaseOrder::where('purchase_request_number',$data->number)->first();
        if ($PO) {
          throw new \Exception("Maaf PR sudah digunakan di PO, Data tidak dapat di ubah lagi.");
        }
        $purchase_request_detail=[];
        if ($request->purchase_request_details) {
          $purchase_request_details = json_decode($request->purchase_request_details,true);
        }else {
          throw new \Exception("Silahkan masukkan data terlebih dahulu");
        }
        if (count($purchase_request_details)==0) {
          throw new \Exception("Silahkan masukkan data terlebih dahulu");
        }

        if ($purchase_request_details) {
          $materials=[];
          $purchase_request_detail_db = \App\Model\PurchaseRequestDetail::where("purchase_request_number",$number)->get();

          if (count($purchase_request_details) !== count($purchase_request_detail_db)) {
            throw new \Exception("Maaf ada pembaharuan mohon refresh browser anda");
          }

          $total_approved_qty = 0;

          foreach ($purchase_request_details as $key => $value) {
            $ordinal = $key + 1;

            $rules = [
              'approved_qty' => 'required|numeric',
            ];

            $messages=[
              'approved_qty.required' => 'Quantity yang diminta tidak boleh kosong',
              'approved_qty.numeric' => 'Quantity yang diminta harus angka',
            ];

            $validator = \Validator::make($value,$rules,$messages);
            if ($validator->fails()) {
              foreach ($validator->messages()->all() as $k => $v) {
                throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
              }
            }

            $material_code = $value["material_code"];

            $purchase_request_detail = \App\Model\PurchaseRequestDetail::where("ordinal",$ordinal)->where("material_code",$material_code)->where("purchase_request_number",$number)->first();
            if (!$purchase_request_detail) {
              throw new \Exception("ada data yang di ubah , harap di refresh kembali browser anda");
            }else {

              if ($value["approved_qty"] > $purchase_request_detail->requested_qty) {
                throw new \Exception("Baris Data Ke-".$ordinal." Maaf Quantity melebihi qty yang di minta");
              }

              \App\Model\PurchaseRequestDetail::where("ordinal",$ordinal)->where("purchase_request_number",$number)->update([
                "admin_code" => $admin_code,
                "approved_qty" => $value['approved_qty'],
              ]);
            }

            $total_approved_qty+=$value['approved_qty'];
          }

          if ($total_approved_qty > 0) {
            $data->approver_code = $this->admin->code;
            $data->save();
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

      //
      // if (!in_array($this->admin->role->title,["Manager Lapangan","Developer"])) {
      //   throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      // }
      //
      // $id = $request->id;
      // if ($id=="") {
      //   throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
      // }
      //
      // $project_belum_selesai = \App\Model\Project::where("id",$request->project_id)->where("is_finished",false)->first();
      // if (!$project_belum_selesai) {
      //   throw new MyException("Maaf project sudah selesai tidak dapat di gunakan lagi");
      // }
      // if ($project_belum_selesai->in_charge_id!==$this->admin->id) {
      //   throw new MyException("Maaf anda bukan penanggung jawab project ini");
      // }
      //
      // $data = PurchaseRequest::where("id",$id)->where("created_by",$this->admin->id)->first();
      // if (!$data) {
      //   throw new MyException("Maaf Data Tidak Ditemukan");
      // }
      // if ($data->status!=="dibuat") {
      //   throw new MyException("Maaf Data Ini Sudah Tidak Dapat Diubah Lagi");
      // }
      // $data->admin_id=$this->admin->id;
      // $data->project_id=$request->project_id;
      //
      // if ($data->save()) {
      //   return response()->json([
      //     "message"=>"Data berhasil di ubah",
      //     "data"=>new PurchaseRequestResource($data)
      //   ],200);
      // }


    }

    public function getAvailableQty(Request $request)
    {
      $rules = [
        'number' => 'required|exists:App\Model\PurchaseRequest,number',
      ];

      $messages=[
        'number.required' => 'PR No harus ada',
        'number.exists' => 'PR No tidak terdaftar',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new ValidationException($validator);
      }
      $exclude_purchase_order_number = $request->purchase_order_number;
      // $data = new PurchaseRequestDetail->where("purchase_request_number",$request->number)->with([
      //   'requester'=>function($q){
      //     $q->with(['employee']);
      //   },
      //   'project',
      //   'purchase_request_details'=>function($q){
      //     $q->with(['material']);
      //   }
      // ])->first();

      $data=[];
      $prds = \App\Model\PurchaseRequestDetail::where("purchase_request_number",$request->number)->get();
      foreach ($prds as $key => $prd) {
        $data[$prd->material_code]=[
          "code"=>$prd->material->code,
          "name"=>$prd->material->name,
          "approved_qty"=>$prd->approved_qty,
          "available_qty"=>$prd->approved_qty,
          "satuan"=>$prd->material->satuan,
        ];
      }

      $pods = \App\Model\PurchaseOrderDetail::whereIn("purchase_order_number",function ($q) use($request,$exclude_purchase_order_number){
        $q->select('number')->from('purchase_orders')->where('purchase_request_number',$request->number);
        if ($exclude_purchase_order_number) {
          $q->where("number","!=",$exclude_purchase_order_number);
        }
      })->get();

      foreach ($pods as $key => $pod) {
        $data[$pod->material_code]["available_qty"] -= $pod->qty;
      }

      $result=[];
      foreach ($data as $key => $value) {
        array_push($result,$value);
      }

      return response()->json([
        "data"=>$result,
      ],200);
    }


    public function cetak(Request $request)
    {

      $number = $request->number;
      $filename = $request->filename ?? "tx-".MyLib::timestamp();
      $pr = \App\Model\PurchaseRequest::find($number);
      $prs = $pr->purchase_request_details;

      $company = new MyLib();
      $mime=MyLib::mime("pdf");

      $pdf = PDF::loadView('laporan.purchase_request', ["pr"=>$pr, "prs"=>$prs,"company"=>$company->company])
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



    public function cetak_approve(Request $request)
    {

      $number = $request->number;
      $filename = $request->filename ?? "tx-".MyLib::timestamp();
      $pr = \App\Model\PurchaseRequest::find($number);
      $prs = $pr->purchase_request_details;

      $company = new MyLib();
      $mime=MyLib::mime("pdf");
      $pdf = PDF::loadView('laporan.purchase_request_approve', ["pr"=>$pr, "prs"=>$prs,"company"=>$company->company])
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
