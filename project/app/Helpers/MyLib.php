<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use File;
use Request;
use App\Exceptions\MyException;
use App\Model\User;
class MyLib {

    public $company = [
      "logo"=>"img/logo.png",
      "name"=>"PT.LIMANTORO AGUNG PROPERTY",
      "address"=>"Jl. Cemara Asri no.22",
      "phone_number"=>"Telp : 061 4123 421 / 0814-8765-0978",
    ];

    public static function admin()
    {
      $token = Request::bearerToken();
      if ($token=="") {
        throw new MyException("Maaf anda tidak teridentifikasi");
      }

      $admin = User::where("api_token",$token)->first();
      if (!$admin) {
        throw new MyException("Maaf data yang dimasukkan tidak valid");
      }
      //unregister user
      if ($admin->role->id == -1) {
        throw new MyException("Maaf anda belum mendapatkan peran apa pun harap hubungi admin");
      }

      if ($admin->can_login == 0) {
        throw new MyException("Maaf Akun anda tidak diizinkan");
      }

      if ($admin->is_active == 0) {
        throw new MyException("Maaf Akun anda tidak aktif, hubungi admin");
      }

      return $admin;
    }

    public static function timestamp()
    {
      $date=new \DateTime();
      return $date->format("YmdHisv");
    }

    public static function mime($ext)
    {
      $result=[
          "contentType"=>"",
          "exportType"=>"",
          "dataBase64"=>""
      ];

      switch ($ext) {
        case 'csv':
        $result["contentType"]="application/csv";
        $result["exportType"]=\Maatwebsite\Excel\Excel::CSV;
        break;

        case 'xls':
        $result["contentType"]="application/vnd.ms-excel";
        $result["exportType"]=\Maatwebsite\Excel\Excel::XLSX;
        break;

        case 'xlsx':
        $result["contentType"]="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        $result["exportType"]=\Maatwebsite\Excel\Excel::XLSX;
        break;

        case 'pdf':
        $result["contentType"]="application/pdf";
        $result["exportType"]=\Maatwebsite\Excel\Excel::DOMPDF;
        break;

        default:
          // code...
          break;
      }

      $result["dataBase64"]="data:".$result["contentType"].";base64,";
      return $result;

    }
}
