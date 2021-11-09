<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use Excel;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;

use App\Model\Project;

use App\Http\Requests\ProjectStore;
use App\Http\Requests\ProjectReq;
use App\Http\Resources\ProjectResource;
use App\Exports\EmployeeReport;

use App\Helpers\MyLib;
use Image;
use File;
use DB;
use Illuminate\Validation\Rule;

class Projects extends Controller
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
      $projects = Project::offset($offset)->limit($limit);
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

        if (isset($sortList["name"])) {
          $projects = $projects->orderBy("name",$sortList["name"]);
        }

        if (isset($sortList["type"])) {
          $projects = $projects->orderBy("type",$sortList["type"]);
        }

        if (isset($sortList["date"])) {
          $projects = $projects->orderBy("date",$sortList["date"]);
        }

        if (isset($sortList["created_at"])) {
          $projects = $projects->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $projects = $projects->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $projects = $projects->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $projects = $projects->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.username")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $projects = $projects->orderBy('code','ASC');
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

        if (isset($likeList["code"])) {
          $projects = $projects->where("code","like",$likeList["code"]);
        }

        if (isset($likeList["name"])) {
          $projects = $projects->orWhere("name","like",$likeList["name"]);
        }


      }

      // ==============
      // Model Filter
      // ==============

      // if (isset($request->code)) {
      //   $projects = $projects->where("code",'like','%'.$request->code.'%');
      // }
      if (isset($request->is_finished)) {
        $projects = $projects->where("is_finished",$request->is_finished);
      }

      // if (isset($request->admin_id)) {
      //   $users = $users->where("admin_id",'like','%'.$request->admin_id.'%');
      // }


      //Rules Manager Lapangan hanya dapat memilih project yg di kepalai
      if (in_array($this->admin->role->title,["Manager Lapangan"])) {
          $projects = $projects->where("in_charge_code",$this->admin->code);
      }



      $projects=$projects->with([
        'in_charge'=>function($q){
          $q->with("employee");
        }
      ]);

      return response()->json([
        "data"=>ProjectResource::collection($projects->get()),
        // "toSql"=>$projects->toSql(),
      ],200);
    }

    public function store(ProjectReq $request)
    {

      DB::beginTransaction();

      try {
        $project_code = $request->code;
        $admin_code = $this->admin->code;

        $data=new Project();
        $data->admin_code=$admin_code;
        $data->code=$project_code;
        $data->name=$request->name;
        $data->type=$request->type;
        $data->date=$request->date;
        $data->in_charge_code=$request->in_charge_code;
        $data->is_finished=$request->is_finished;
        $data->address=$request->address;
        $data->save();

        $unit=[];
        if (!$request->units) {
            throw new \Exception("Masukkan Data Unit");
        }
        $units = json_decode($request->units,true);
        if (count($units)==0) {
          throw new \Exception("Masukkan Data Unit");
        }

        foreach ($units as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'name' => 'required|min:3',
            'land_size' => 'required|min:3',
            'building_size' => 'required|min:3',
            'builded' => 'required|numeric',
            'sold' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'marketing_fee' => 'required|numeric',
            // 'project_id' => 'required|exists:App\Model\Project,id',
          ];

          $messages=[
            // 'id.required' => 'ID tidak boleh kosong',
            'name.required' => 'Nama tidak boleh kosong',
            'name.min' => 'Nama minimal 3 karakter',
            'land_size.required' => 'Ukuran tanah tidak boleh kosong',
            'land_size.min' => 'Ukuran tanah minimal 3 karakter',
            'building_size.required' => 'Ukuran banguanan tidak boleh kosong',
            'building_size.min' => 'Ukuran banguanan minimal 3 karakter',

            'builded.required' => 'Jumlah unit di bangun tidak boleh kosong',
            'builded.numeric' => 'Jumlah unit di bangun harus berupa angka',

            'sold.required' => 'Jumlah unit terjual tidak boleh kosong',
            'sold.numeric' => 'Jumlah unit terjual harus berupa angka',

            'selling_price.required' => 'Harga jual tidak boleh kosong',
            'selling_price.numeric' => 'Harga jual harus berupa angka',

            'marketing_fee.required' => 'Marketing fee tidak boleh kosong',
            'marketing_fee.numeric' => 'Marketing fee harus berupa angka',

            'project_id.required' => 'Project tidak boleh kosong',
            'project_id.exists' => 'Project yang di pilih tidak sesuai',


            // 'is_finished.required' => 'Status Project tidak boleh kosong',
            // 'is_finished.in' => 'Data yang di input tidak sesuai',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }


          $unit = new \App\Model\Unit();
          $unit->ordinal = $ordinal;
          $unit->name = $value['name'];
          $unit->land_size = $value['land_size'];
          $unit->building_size = $value['building_size'];
          $unit->builded = $value['builded'];
          $unit->sold = $value['sold'];
          $unit->selling_price = $value['selling_price'];
          $unit->marketing_fee = $value['marketing_fee'];
          $unit->project_code = $project_code;
          $unit->admin_code = $admin_code;
          $unit->save();
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


    public function update(ProjectReq $request)
    {

      DB::beginTransaction();

      try {
        $project_code = $request->code;
        $admin_code = $this->admin->code;
        $new_code = $request->new_code;

        $data=Project::where("code",$project_code)->first();
        $data->admin_code=$admin_code;
        $data->name=$request->name;
        $data->type=$request->type;
        $data->date=$request->date;
        $data->address=$request->address;
        $data->in_charge_code=$request->in_charge_code;
        $data->is_finished=$request->is_finished;

        if ($new_code && $new_code!=$project_code) {
          $data->code=$new_code;
        }

        $data->save();

        $unit=[];
        if (!$request->units) {
          throw new \Exception("Masukkan Data Unit");
        }
        $units = json_decode($request->units,true);
        if (count($units)==0) {
          throw new \Exception("Masukkan Data Unit");
        }

        \App\Model\Unit::where("project_code",$new_code)->delete();
        foreach ($units as $key => $value) {
          $ordinal = $key + 1;

          $rules = [
            'name' => 'required|min:3',
            'land_size' => 'required|min:3',
            'building_size' => 'required|min:3',
            'builded' => 'required|numeric',
            'sold' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'marketing_fee' => 'required|numeric',
            // 'project_id' => 'required|exists:App\Model\Project,id',
          ];

          $messages=[
            // 'id.required' => 'ID tidak boleh kosong',
            'name.required' => 'Nama tidak boleh kosong',
            'name.min' => 'Nama minimal 3 karakter',
            'land_size.required' => 'Ukuran tanah tidak boleh kosong',
            'land_size.min' => 'Ukuran tanah minimal 3 karakter',
            'building_size.required' => 'Ukuran banguanan tidak boleh kosong',
            'building_size.min' => 'Ukuran banguanan minimal 3 karakter',

            'builded.required' => 'Jumlah unit di bangun tidak boleh kosong',
            'builded.numeric' => 'Jumlah unit di bangun harus berupa angka',

            'sold.required' => 'Jumlah unit terjual tidak boleh kosong',
            'sold.numeric' => 'Jumlah unit terjual harus berupa angka',

            'selling_price.required' => 'Harga jual tidak boleh kosong',
            'selling_price.numeric' => 'Harga jual harus berupa angka',

            'marketing_fee.required' => 'Marketing fee tidak boleh kosong',
            'marketing_fee.numeric' => 'Marketing fee harus berupa angka',

            'project_id.required' => 'Project tidak boleh kosong',
            'project_id.exists' => 'Project yang di pilih tidak sesuai',


            // 'is_finished.required' => 'Status Project tidak boleh kosong',
            // 'is_finished.in' => 'Data yang di input tidak sesuai',
          ];

          $validator = \Validator::make($value,$rules,$messages);
          if ($validator->fails()) {
            foreach ($validator->messages()->all() as $k => $v) {
              throw new \Exception("Baris Data Ke-".$ordinal." ".$v);
            }
          }

          $unit = new \App\Model\Unit();
          $unit->ordinal = $ordinal;
          $unit->name = $value['name'];
          $unit->land_size = $value['land_size'];
          $unit->building_size = $value['building_size'];
          $unit->builded = $value['builded'];
          $unit->sold = $value['sold'];
          $unit->selling_price = $value['selling_price'];
          $unit->marketing_fee = $value['marketing_fee'];
          $unit->project_code = $new_code;
          $unit->admin_code = $admin_code;
          $unit->save();
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

    // public function update(ProjectReq $request)
    // {
    //   $user = \App\Model\User::where("id",$request->in_charge_id)->where("role_id",3)->first();
    //   if (!$user) {
    //     throw new MyException("Maaf User Tidak Terdaftar Sebagai Manager Lapangan");
    //   }
    //
    //   $id = $request->id;
    //   if ($id=="") {
    //     throw new MyException("Parameter yang dikirim tidak sesuai. Refresh browser Anda dan ulangi kembali");
    //   }
    //
    //   $data = Project::where("id",$id)->first();
    //   $data->admin_id=$this->admin->id;
    //   $data->name=$request->name ?? $data->name;
    //   $data->is_finished=$request->is_finished ?? $data->is_finished;
    //
    //   if ($data->save()) {
    //     return response()->json([
    //       "message"=>"Data berhasil di ubah",
    //       "data"=>new ProjectResource($data)
    //     ],200);
    //   }
    // }

    public function show(Request $request)
    {

      $rules = [
         'code' => 'required|exists:App\Model\Project,code',
      ];

      $messages=[
         'code.required' => 'Nomor Code Project Harus Ada',
         'code.exists' => 'Nomor Code Project tidak terdaftar',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);
      if ($validator->fails()) {
        throw new MyException($validator->messages()->all()[0]);
      }

      $data = Project::where("code",$request->code)->first();

      return response()->json([
        "data"=>new ProjectResource($data->loadMissing(['in_charge','units'])),
      ],200);
    }
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

  public function cetak(Request $request)
  {

    $code = $request->code;
    $filename = $request->filename ?? "tx-".MyLib::timestamp();
    $data = \App\Model\Project::find($code);
    $inject_mats = \App\Model\MaterialControl::where("status","Inject")->get();
    $rest_mats = \App\Model\MaterialControl::where("status","Rest")->get();

    $purchase_requests_number = $data->purchase_requests->pluck('number');

    $pos = \App\Model\PurchaseOrder::whereIn("purchase_request_number",$purchase_requests_number)->get();

    $supplier_materials = [];
    $purchase_order_details = \App\Model\PurchaseOrderDetail::whereIn("purchase_order_number",function ($q)use($purchase_requests_number){
      $q->select("number");
      $q->from("purchase_orders");
      $q->whereIn("purchase_request_number",$purchase_requests_number);
    })->get();

    foreach ($purchase_order_details as $pod) {
      array_push($supplier_materials,[
        "purchase_order_number"=>$pod->purchase_order->number,
        "supplier_code"=>$pod->purchase_order->supplier_code,
        "supplier_name"=>$pod->purchase_order->supplier->name,
        "material_code"=>$pod->material_code,
        "material_name"=>$pod->material->name,
        "material_satuan"=>$pod->material->satuan,
        "material_qty"=>$pod->qty,
        "material_qty_return"=>0,
        "material_price"=>$pod->price,
      ]);
    }

    $purchase_return_details = \App\Model\PurchaseReturnDetail::whereIn("purchase_return_number",function ($q)use($purchase_requests_number){
      $q->select("number");
      $q->from("purchase_returns");
      $q->whereIn("purchase_order_number",function($q)use($purchase_requests_number){
        $q->select("number");
        $q->from("purchase_orders");
        $q->whereIn("purchase_request_number",$purchase_requests_number);
      });
    })->get();

    foreach ($purchase_return_details as $prd) {
      foreach ($supplier_materials as $k => $supplier_material) {
        if (
          $prd->purchase_return->purchase_order_number==$supplier_material["purchase_order_number"] &&
          $prd->purchase_return->supplier_code==$supplier_material["supplier_code"] &&
          $prd->material_code==$supplier_material["material_code"]
        ) {
          $supplier_materials[$k]["material_qty_return"]+=$prd->qty;
        }
      }
    }


    foreach ($supplier_materials as $key => $sm) {
      $supplier_materials[$key]["material_total"]=($sm["material_qty"] - $sm["material_qty_return"])*$sm["material_price"];
    }


    $sup_grup = [];

    foreach ($supplier_materials as $sm) {

      if (count($sup_grup)==0 || gettype(array_search($sm["supplier_code"],array_map(function($x){ return $x["supplier"]["code"];},$sup_grup)))!=="integer") {
        array_push($sup_grup,[
            "supplier"=>[
              "code"=>$sm['supplier_code'],
              "name"=>$sm['supplier_name']
            ],
            "materials"=>[],
            "total"=>0
        ]);
      }

      $idxOf= array_search($sm["supplier_code"],array_map(function($x){ return $x["supplier"]["code"];},$sup_grup));

      $sup_grup_mat = $sup_grup[$idxOf]["materials"];

      $nk=-1;
      foreach ($sup_grup_mat as $key => $sgm) {
        if ($sgm["code"]==$sm["material_code"] && $sgm["price"]==$sm["material_price"]) {
          $nk=$key;
          break;
        }
      }
      if ($nk==-1) {
        array_push($sup_grup[$idxOf]["materials"],[
            "code"=>$sm["material_code"],
            "name"=>$sm["material_name"],
            "satuan"=>$sm["material_satuan"],
            "qty"=>$sm["material_qty"],
            "qty_return"=>$sm["material_qty_return"],
            "total"=>$sm["material_total"]
        ]);

        $sup_grup[$idxOf]["total"]=$sm["material_total"];
      }else {
        $sup_grup[$idxOf]["materials"][$nk]["qty"]+=$sm["material_qty"];
        $sup_grup[$idxOf]["materials"][$nk]["qty_return"]+=$sm["material_qty_return"];
        $sup_grup[$idxOf]["materials"][$nk]["total"]+=$sm["material_total"];
        $sup_grup[$idxOf]["total"]+=$sm["material_total"];
      }

    }


    $mat_grup = [];
    foreach ($supplier_materials as $sm) {

      if (count($mat_grup)==0 || gettype(array_search($sm["material_code"],array_map(function($q){ return $q['code'];},$mat_grup)))!=="integer") {
        array_push($mat_grup,[
          "code"=>$sm["material_code"],
          "name"=>$sm["material_name"],
          "satuan"=>$sm["material_satuan"],
          "qty"=>$sm["material_qty"]-$sm["material_qty_return"],
        ]);
      }else {
        $idxOf=array_search($sm["material_code"],array_map(function($q){ return $q['code'];},$mat_grup));
        $mat_grup[$idxOf]["qty"]+=$sm["material_qty"]-$sm["material_qty_return"];
      }
    }

    foreach ($inject_mats as $key=> $im) {

      if (gettype(array_search($im->material->code,array_map(function($q){ return $q['code'];},$mat_grup)))!=="integer" ) {
        array_push($mat_grup,[
          "code"=>$im->material->code,
          "name"=>$im->material->name,
          "satuan"=>$im->material->satuan,
          "qty"=>$im->qty,
        ]);
      }else {
        $idxOf=array_search($im->material->code,array_map(function($q){ return $q['code'];},$mat_grup));
        $mat_grup[$idxOf]["qty"]+=$im->qty;
      }
    }

    $company = new MyLib();
    $mime=MyLib::mime("pdf");

    $pdf = PDF::loadView('laporan.project', ["data"=>$data, "company"=>$company->company,"inject_mats"=>$inject_mats,"rest_mats"=>$rest_mats,"sup_grup"=>$sup_grup,"mat_grup"=>$mat_grup])
    ->setPaper('a4', 'portrait');

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
