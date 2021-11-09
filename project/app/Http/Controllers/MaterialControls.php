<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

use App\Model\MaterialControl;

use App\Http\Requests\MaterialControlReq;
use App\Http\Resources\MaterialControlResource;

use App\Helpers\MyLib;
use DB;
use PDF;

class MaterialControls extends Controller
{
    private $admin;

    public function __construct()
    {
        $this->admin = MyLib::admin();
    }

    public function index(MaterialControlReq $request)
    {
      $project_code = $request->project_code;
      $status = $request->status;
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
      $data = MaterialControl::where("project_code",$project_code)->where("status",$status)->offset($offset)->limit($limit);

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
        //   $data = $data->orderBy("created_at",$sortList["created_at"]);
        // }
        //
        // if (isset($sortList["updated_at"])) {
        //   $data = $data->orderBy("updated_at",$sortList["updated_at"]);
        // }

        // if (isset($sortList["role"])) {
        //   $employees = $employees->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $data = $data->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $data = $data->orderBy('ordinal','asc');
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
          $data = $data->where("number","like",$likeList["number"]);
        }

        if (isset($likeList["project_code"])) {
          $data = $data->orWhere("project_code","like",$likeList["project_code"]);
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
      // $data=$data->get();


      return response()->json([
        "data"=>MaterialControlResource::collection($data->with([
          // 'requester'=>function($q){
          //   $q->with(['employee']);
          // },
          'project',
          'material'
        ])->get()),
      ],200);
    }

    public function store(MaterialControlReq $request)
    {
      if (!in_array($this->admin->role->title,["Purchasing","Developer"])) {
        throw new MyException("Maaf Peran Anda Tidak Diizinkan Untuk Menambah Data");
      }

      DB::beginTransaction();
      try {
        $admin_code = $this->admin->code;
        $project_code = $request->project_code;
        $status = $request->status;

        if (!$request->material_controls) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        $material_controls = json_decode($request->material_controls,true);
        if (count($material_controls) == 0) {
          throw new \Exception("Silahkan masukkan data detail");
        }

        \App\Model\MaterialControl::where("project_code",$project_code)->where("status",$status)->delete();

        $materials= [];
        foreach ($material_controls as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'material_code' => 'required|exists:App\Model\Material,code',
            'qty' => 'required|min:1|numeric',
          ];

          $messages=[
            'material_code.required' => 'Material harus di pilih',
            'material_code.exists' => 'Material tidak terdaftar',

            'qty.required' => 'Quantity tidak boleh kosong',
            'qty.min' => 'Quantity minimal 1',
            'qty.numeric' => 'Quantity harus angka',
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

          $purchase_request_detail = new \App\Model\MaterialControl();
          $purchase_request_detail->admin_code = $admin_code;
          $purchase_request_detail->project_code = $project_code;
          $purchase_request_detail->ordinal = $ordinal;
          $purchase_request_detail->material_code = $value['material_code'];
          $purchase_request_detail->qty = $value['qty'];
          $purchase_request_detail->status = $status;
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
    }



    // public function show(Request $request)
    // {
    //
    //   $rules = [
    //     'project_code' => 'required|exists:App\Model\Project,code',
    //   ];
    //
    //   $messages=[
    //     'project.required' => 'Project No harus ada',
    //     'project.exists' => 'Project No tidak terdaftar',
    //   ];
    //
    //   $admin_code = $this->admin->code;
    //   $project_code = $request->project_code;
    //   $status = $request->status;
    //
    //   $validator = \Validator::make($request->all(),$rules,$messages);
    //   if ($validator->fails()) {
    //     throw new ValidationException($validator);
    //   }
    //
    //   $data = new MaterialControl::where("project_code",$project_code)->where("status",$status)->with([
    //     'project',
    //   ])->get();
    //
    //   if (count($data)>0) {
    //       throw new MyException("Maaf Data Tidak Ditemukan");
    //   }
    //
    //   return response()->json([
    //     "data"=>new MaterialControlResource($data),
    //   ],200);
    // }


    // public function cetak(Request $request)
    // {
    //
    //   $number = $request->number;
    //   $filename = $request->filename ?? "tx-".MyLib::timestamp();
    //   $pr = \App\Model\MaterialControl::find($number);
    //   $prs = $pr->purchase_request_details;
    //
    //   $company = new MyLib();
    //   $mime=MyLib::mime("pdf");
    //
    //   $pdf = PDF::loadView('laporan.purchase_request', ["pr"=>$pr, "prs"=>$prs,"company"=>$company->company])
    //   ->setPaper('a4', 'landscape');
    //
    //   // $pdf = PDF::loadView('laporan.material', ["data"=>$employees, "company"=>$company->company])->setPaper('a4', 'portrait')->setOptions(['isPhpEnabled' => true,'isJavascriptEnabled'=>true,'javascriptDelay'=>13500]);
    //   // $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('laporan.material', ["data"=>$employees, "company"=>$company->company,"b62"=>$base64,"pp"=>$public_path])->setPaper('a4', 'portrait');
    //   // $pdf->output();
    //   // $dom_pdf = $pdf->getDomPDF();
    //   //
    //   // $canvas = $dom_pdf ->get_canvas();
    //   // $canvas->page_text(0, 0, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));
    //   $bs64=base64_encode($pdf->download($filename.'.pdf'));
    //
    //   $result =[
    //     "contentType"=>$mime["contentType"],
    //     "data"=>$bs64,
    //     "dataBase64"=>$mime["dataBase64"].$bs64,
    //     "filename"=>$filename
    //   ];
    //
    //   return $result;
    // }
    //
    //
    //
    // public function cetak_approve(Request $request)
    // {
    //
    //   $number = $request->number;
    //   $filename = $request->filename ?? "tx-".MyLib::timestamp();
    //   $pr = \App\Model\MaterialControl::find($number);
    //   $prs = $pr->purchase_request_details;
    //
    //   $company = new MyLib();
    //   $mime=MyLib::mime("pdf");
    //   $pdf = PDF::loadView('laporan.purchase_request_approve', ["pr"=>$pr, "prs"=>$prs,"company"=>$company->company])
    //   ->setPaper('a4', 'landscape');
    //
    //   // $pdf = PDF::loadView('laporan.material', ["data"=>$employees, "company"=>$company->company])->setPaper('a4', 'portrait')->setOptions(['isPhpEnabled' => true,'isJavascriptEnabled'=>true,'javascriptDelay'=>13500]);
    //   // $pdf = PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('laporan.material', ["data"=>$employees, "company"=>$company->company,"b62"=>$base64,"pp"=>$public_path])->setPaper('a4', 'portrait');
    //   // $pdf->output();
    //   // $dom_pdf = $pdf->getDomPDF();
    //   //
    //   // $canvas = $dom_pdf ->get_canvas();
    //   // $canvas->page_text(0, 0, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 10, array(0, 0, 0));
    //   $bs64=base64_encode($pdf->download($filename.'.pdf'));
    //
    //   $result =[
    //     "contentType"=>$mime["contentType"],
    //     "data"=>$bs64,
    //     "dataBase64"=>$mime["dataBase64"].$bs64,
    //     "filename"=>$filename
    //   ];
    //
    //   return $result;
    // }
}
