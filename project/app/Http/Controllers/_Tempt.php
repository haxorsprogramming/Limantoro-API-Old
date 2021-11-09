<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Str;
use Hash;
// use App\Jobs\SendVerificationEmail;
use Illuminate\Support\Facades\Validator;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;

use App\Model\User;
use App\Helpers\MyLib;
use Illuminate\Validation\Rule;


class Users extends Controller
{
  private $admin;

  public function __construct()
  {
  }

  public function index(Request $request)
  {
    $limit =  ? $request->limit : 250; // Limit +> Much Data
    if (isset($request->limit)) {
      if ($request->limit <= 250) {
        $limit = $request->limit,
      }else {
        throw new MyException("Max Limit 250");
      }
    }
    $offset = isset($request->offset) ? $request->offset : 0; // example offset 400 start from 401
    $page = isset($request->page) ? $request->page : 1;

    $this->admin = MyLib::admin();

    $rules = [
       'page' => 'required|numeric',
       'type'=>[
         'required',
         Rule::in(['limit', 'all']),
       ],
    ];

    $messages=[
       'page.required' => 'Mohon Refresh Browser Anda',
       'page.numeric' => 'Mohon Refresh Browser Anda',

       'type.required' => 'Mohon Refresh Browser Anda',
       'type.in' => 'Mohon Refresh Browser Anda',
    ];

    $validator = \Validator::make($request->all(),$rules,$messages);

    if ($validator->fails()) {
      throw new MyException("Mohon Refresh Browser Anda");
    }


    $type=$request->type;
    $limit = 10;

    if ($type=="limit") {
      $page = $request->page ?? 1;
      // LIMIT => Banyaknya data perhalaman
      $req_limit = $request->limit;
      if ($req_limit && $req_limit > 50) {
        throw new MyException("Maaf Batas Pengambilan Data Per Halaman Maksimal adalah 50 Baris Data");
      }elseif ($req_limit && $req_limit <= 50) {
        $limit = $req_limit;
      }

      // OFFSET => Memuat halaman dari data ke berapa
      $offset = 0;
      $offset = ($page*$limit)-$limit;

      // Words => Kata/Kalimat yang akan dicari
      $users = User::offset($offset)->limit($limit)->where("id","!=",0)->orderBy('username','ASC');

    }else {
      $users = User::where("id","!=",0)->orderBy('username','ASC');
    }

    // ==============
    // specifict thing
    // ==============

    if ($by_role = (int)$request->by_role) {
      if ($by_role==0) {
      }else {
        $users->where("role_id",$by_role);
      }
    }




    $req_words = $request->words;
    if ($req_words) {
      $users = $users->where('username','like','%'.$req_words.'%');
    }

    $users=$users->get();


    $record=count($users);
    // if -1 (No Record / Not Found) 0 (No More Record) > 0 (Have Record)
    if($type=="limit" && $page==1 && $record==0){
      $record=-1;
    }

    return response()->json([
      "data"=>UserResource::collection($users),
      "record"=>$record,
      "limit"=>$limit
    ],200);
  }
    public function store(Request $request)
    {
      $this->admin = MyLib::admin();

      $rules = [
         'username' => 'required|min:3',
         'password'=>"required|min:8",
         'confirm_password'=>"required|min:8|same:password",
         'is_active'=>"boolean",
         'role_id'=>"required|exists:roles,id"
      ];

      $messages=[
         'username.required' => 'Nama Pengguna tidak boleh kosong',
         'username.min' => 'Nama pengguna minimal 3 karakter',

         'password.required' => 'Kata Sandi tidak boleh kosong',
         'password.min' => 'Kata Sandi minimal 8 Karakter',

         'confirm_password.required' => 'Ulang Kata Sandi tidak boleh kosong',
         'confirm_password.same' => 'Kata Sandi tidak cocok',
         'confirm_password.min' => 'Kata Sandi minimal 8 Karakter',

         'is_active.boolean' => 'Status Pengguna hanya boleh di pilih',
         "role_id.exists"=>"Role tidak terdaftar"
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      // $token = $request->bearerToken();
      // if ($token=="") {
      //   throw new MyException("Get user info cannot complete, please refresh your browser then try it again");
      // }
      //
      // $admin = User::where("api_token",$token)->first();
      // if (!$admin) {
      //   throw new MyException("Maaf data yang dimasukkan tidak valid");
      // }


      $username = $request->username;
      $password = $request->password;

      $user = User::where("username",$username)->first();
      if ($user) {
        throw new MyException("Maaf Nama Pengguna Ini Sudah Terdaftar");
      }

      $user=new User();
      $user->username=strtolower(trim($username));
      $user->password=bcrypt($password);
      $user->admin_id=$this->admin->id;
      $user->role_id=$request->role_id;
      $user->image=null;
      $user->is_active=(int)$request->is_active;


      if ($user->save()) {
        return response()->json([
          "message"=>"Nama pengguna berhasil di tambahkan",
          "data"=>new UserResource($user)
        ],200);
      }
    }

    public function update(Request $request)
    {
      $this->admin = MyLib::admin();

      $rules = [
         'username' => 'required|min:3',
         'password'=>"nullable|min:8",
         'confirm_password'=>"required_if:password,present|same:password",
         'is_active'=>"boolean",
         'role_id'=>"required|exists:roles,id"

      ];

      $messages=[
         'username.required' => 'Nama Pengguna tidak boleh kosong',
         'username.min' => 'Nama pengguna minimal 3 karakter',

         // 'password.required' => 'Kata Sandi tidak boleh kosong',
         'password.min' => 'Kata Sandi minimal 8 Karakter',

         'confirm_password.required_if' => 'Ulang Kata Sandi tidak boleh kosong',
         'confirm_password.same' => 'Kata Sandi tidak cocok',
         // 'confirm_password.min' => 'Kata Sandi minimal 8 Karakter',

         'is_active.boolean' => 'Status Pengguna hanya boleh di pilih',
         "role_id.exists"=>"Role tidak terdaftar"

      ];

      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      // $token = $request->bearerToken();
      // if ($token=="") {
      //   throw new MyException("Get user info cannot complete, please refresh your browser then try it again");
      // }
      //
      // $admin = User::where("api_token",$token)->first();
      // if (!$admin) {
      //   throw new MyException("Maaf data yang dimasukkan tidak valid");
      // }

      $identity=$request->identity;
      if ($identity=="") {
        throw new MyException("Maaf parameter yang dikirimkan tidak sesuai, harap ulangi kembali");
      }

      $username = $request->username;
      $password = $request->password;

      $user = User::where("id","!=",0)->where("username",$identity)->first();
      if (!$user) {
        throw new MyException("Data Berhasil Diubah");
      }
      $user->admin_id=$this->admin->id;
      $user->role_id=$request->role_id;


      if ($password) {
        $user->password=bcrypt($password);
      }
      $user->username=$request->username;
      $user->is_active=(int)$request->is_active;

      if ($user->save()) {
        return response()->json([
          "message"=>"Nama pengguna berhasil di ubah",
          "data"=>new UserResource($user)
        ],200);
      }
    }

    // public function verify($email_token)
    // {
    //   try {
    //     // $rules = [
    //     //    'email' => 'required|email',
    //     //    'password'=>"required|min:8",
    //     //    'confirm_password'=>"required|min:8|same:password",
    //     // ];
    //     //
    //     // $messages=[
    //     //    'email.required' => 'Email tidak boleh kosong',
    //     //    'email.email' => 'Format Email tidak sesuai',
    //     //
    //     //    'password.required' => 'Password tidak boleh kosong',
    //     //    'password.min' => 'Password minimal 8 Karakter',
    //     //
    //     //    'confirm_password.required' => 'Confirm Password tidak boleh kosong',
    //     //    'confirm_password.same' => 'Password tidak cocok',
    //     //    'confirm_password.min' => 'Password minimal 8 Karakter',
    //     // ];
    //     //
    //     // $validator = \Validator::make($request->all(),$rules,$messages);
    //     //
    //     // if ($validator->fails()) {
    //     //    return response()->json([
    //     //       // "errors"=>$validator->messages(),
    //     //       // "error_all"=>$validator->messages()->all(),
    //     //       "errors"=>$validator->errors(),
    //     //       "error_all"=>$validator->errors()->all(),
    //     //    ],400);
    //     // }
    //     // return response()->json([
    //     //   "message"=>"Link verifikasi salah, harap lakukan verifikasi kembali"
    //     // ],400);
    //
    //     $user = User::where("email_token",$email_token)->first();
    //     if (!$user) {
    //       return response()->json([
    //         "message"=>"Link verifikasi salah, harap lakukan verifikasi kembali"
    //       ],400);
    //     }
    //     $user->email_verify=true;
    //     $user->save();
    //
    //     return response()->json([
    //       "status"=>200,
    //       "message"=>"Email berhasil diverifikasi, Harap Login Kembali ",
    //       "data"=>""
    //     ],200);
    //
    //   } catch (\Exception $e) {
    //     return response()->json([
    //       "message"=>"Maaf Server Sedang Di tindak lanjuti harap kembali lagi nanti"
    //     ],500);
    //   }
    // }

    // public function sendVerifyEmail(Request $request)
    // {
    //   try {
    //     $rules = [
    //        'email' => 'required|email',
    //     ];
    //
    //     $messages=[
    //        'email.required' => 'Email tidak boleh kosong',
    //        'email.email' => 'Format Email tidak sesuai',
    //     ];
    //
    //     $validator = \Validator::make($request->all(),$rules,$messages);
    //
    //     if ($validator->fails()) {
    //        return response()->json([
    //           // "errors"=>$validator->messages(),
    //           // "error_all"=>$validator->messages()->all(),
    //           "errors"=>$validator->errors(),
    //           "error_all"=>$validator->errors()->all(),
    //        ],400);
    //     }
    //
    //     $email = $request->email;
    //     $user = User::where("email",$email)->first();
    //     if (!$user) {
    //       return response()->json([
    //         "message"=>"Maaf Email tidak terdaftar"
    //       ],400);
    //     }
    //
    //     dispatch(new SendVerificationEmail($user));
    //
    //     return response()->json([
    //       "status"=>200,
    //       "message"=>"Link verifikasi berhasil di kirim, mohon check email anda terlebih dahulu",
    //       "data"=>""
    //     ],200);
    //
    //   } catch (\Exception $e) {
    //     return response()->json([
    //       "message"=>"Maaf Server Sedang Di tindak lanjuti harap kembali lagi nanti"
    //     ],500);
    //   }
    // }


    public function login(Request $request)
    {
      // $this->admin = MyLib::admin();

      $rules = [
         'username' => 'required|min:3',
         'password'=>"required|min:8",
      ];

      $messages=[
         'username.required' => 'Nama Pengguna tidak boleh kosong',
         'username.min' => 'Nama Pengguna minimal 3 karakter',

         'password.required' => 'Kata sandi tidak boleh kosong',
         'password.min' => 'Kata sandi minimal 8 Karakter',

      ];


      $validator = Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $username = $request->username;
      $password = $request->password;

      $user = User::where("username",$username)->first();
      if (!$user) {
        throw new MyException("Maaf nama pengguna ini tidak terdaftar");
      }
      if ($user->is_active==0) {
        throw new MyException("Maaf akun ini sedang di non aktifkan harap hubungi admin");
      }

      if (Hash::check($password,$user->password)) {
        $api_token = $user->generateToken();

        return response()->json([
          "token"=>$api_token
        ],200);
      }else {
        throw new MyException("Nama Pengguna dan kata sandi tidak cocok");
      }

    }


    public function logout(Request $request)
    {
      $this->admin = MyLib::admin();

      // $token = $request->bearerToken();
      // if ($token=="") {
      //   throw new MyException("Logout cannot complete, please refresh your browser then try it again");
      // }
      //
      // $user = User::where("api_token",$token)->first();
      // if (!$user) {
      //   throw new MyException("Maaf data yang dimasukkan tidak valid");
      // }

      $this->admin->api_token="";
      $this->admin->save();

      return response()->json([
        "message"=>"Logout Berhasil",
      ],200);
    }

    public function getUser(Request $request)
    {
      $this->admin = MyLib::admin();
      //
      // $token = $request->bearerToken();
      // if ($token=="") {
      //   throw new MyException("Get user info cannot complete, please refresh your browser then try it again");
      // }
      //
      // $user = User::where("api_token",$token)->first();
      // if (!$user) {
      //   throw new MyException("Maaf data yang dimasukkan tidak valid");
      // }

      return response()->json([
        "status"=>200,
        "message"=>"Tampilkan data user",
        "user"=> [
          "username"=>$this->admin->username,
          "scope"=>[$this->admin->role->title]
        ]
      ],200);
    }




}
