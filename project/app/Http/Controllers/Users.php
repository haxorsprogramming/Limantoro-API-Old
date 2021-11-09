<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Str;
use Hash;
use Image;

// use App\Jobs\SendVerificationEmail;
use Illuminate\Support\Facades\Validator;

use App\Exceptions\MyException;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use App\Http\Resources\UsersResourceCollection;
use App\Http\Requests\UserReq;

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

    $this->admin = MyLib::admin();

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
    $users = User::offset($offset)->limit($limit);

    //---- All Developer Hidden
    $users=$users->where("role_id","!=",0);


    //======================================================================================================
    // Model Sorting | Example $request->sort = "code:desc,role:desc";
    //======================================================================================================

    if ($request->sort) {
      $sortList=[];

      $sorts=explode(",",$request->sort);
      foreach ($sorts as $key => $sort) {
        $side = explode(":",$sort);
        $side[1]=isset($side[1])?$side[1]:'ASC';
        $sortList[$side[0]]=$side[1];
      }

      if (isset($sortList["code"])) {
        $users = $users->orderBy("code",$sortList["code"]);
      }

      if (isset($sortList["created_at"])) {
        $users = $users->orderBy("created_at",$sortList["created_at"]);
      }

      if (isset($sortList["updated_at"])) {
        $users = $users->orderBy("updated_at",$sortList["updated_at"]);
      }

      // if (isset($sortList["role"])) {
      //   $users = $users->orderBy(function($q){
      //     $q->from("roles")
      //     ->select("title")
      //     ->whereColumn("id","users.role_id");
      //   },$sortList["role"]);
      // }

      // if (isset($sortList["admin"])) {
      //   $users = $users->orderBy(function($q){
      //     $q->from("users as u")
      //     ->select("u.code")
      //     ->whereColumn("u.id","users.id");
      //   },$sortList["admin"]);
      // }
    }else {
      $users = $users->orderBy('code','ASC');
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
        $employees = $employees->where("code","like",$likeList["code"]);
      }

      // if (isset($likeList["name"])) {
      //   $employees = $employees->orWhere("name","like",$likeList["name"]);
      // }


    }

    // // ==============
    // // Model Filter
    // // ==============
    //
    if (isset($request->role_name)) {
      $users = $users->whereIn("role_id",function($q)use($request){
        $q->select('id')->from("roles")->where("title",$request->role_name);
      });
    }
    // if (isset($request->role_id)) {
    //   $users = $users->where("role_id",'like','%'.$request->role_id.'%');
    // }
    // if (isset($request->admin_code)) {
    //   $users = $users->where("admin_code",'like','%'.$request->admin_code.'%');
    // }

    // $users = $users->where("code",'mal1sss');

    // $user = $users->first();
    // $users=$users->get();

    // $ss = $users->with("employee")->first();
    // return response()->json([
    //   "data"=>$ss->employee,
    // ],200);
    return response()->json([
      "data"=>UserResource::collection($users->get()),
      // "data"=>UserResource::collection($users)->hide(['is_active']),
      // "data"=>UserResource::make($user)->hide(['image']),
      // "toSql"=>$users->toSql()
    ],200);
  }

    public function store(UserReq $request)
    {
      $this->admin = MyLib::admin();

      // $rules = [
      //    'code' => 'required|min:3|unique:\App\Model\User,code',
      //    'password'=>"required|min:8",
      //    'confirm_password'=>"required|min:8|same:password",
      //    'is_active'=>"boolean",
      //    'role_id'=>"required|exists:roles,id"
      // ];
      //
      // $messages=[
      //    'code.required' => 'Nama Pengguna tidak boleh kosong',
      //    'code.min' => 'Nama pengguna minimal 3 karakter',
      //    'code.unique' => 'Maaf Nama Pengguna Ini Sudah Terdaftar',
      //
      //    'password.required' => 'Kata Sandi tidak boleh kosong',
      //    'password.min' => 'Kata Sandi minimal 8 Karakter',
      //
      //    'confirm_password.required' => 'Ulang Kata Sandi tidak boleh kosong',
      //    'confirm_password.same' => 'Kata Sandi tidak cocok',
      //    'confirm_password.min' => 'Kata Sandi minimal 8 Karakter',
      //
      //    'is_active.boolean' => 'Status Pengguna hanya boleh di pilih',
      //    "role_id.exists"=>"Role tidak terdaftar"
      // ];
      //
      // $validator = \Validator::make($request->all(),$rules,$messages);
      //
      // if ($validator->fails()) {
      //   throw new ValidationException($validator);
      // }

      $user=new User();
      $user->code=$request->code;
      if ($request->password) {
        $user->password=bcrypt($request->password);
      }
      $user->admin_code=$this->admin->code;
      $user->role_id=$request->role_id;
      // $user->image=null;
      $user->is_active=(int)$request->is_active;
      $user->can_login=(int)$request->can_login;
      $user->id_number=$request->id_number;
      $user->name=$request->name;
      $user->birth_date=$request->birth_date;
      $user->address=$request->address;
      $user->gender=$request->gender;
      $user->type=$request->type;

      $new_image = $request->file('photo');

      if($new_image != null){
        $date=new \DateTime();
        $timestamp=$date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/','',$timestamp)).'.'.$ext;
        $location = "/img/employees/{$file_name}";
        try {
          ini_set('memory_limit','256M');
          Image::make($new_image)->save(public_path($location));
        } catch (\Exception $e) {
          throw new MyException("Save Photo Failed");

        }

      } else {
          $location = null;
      }
      // return $location;

      $user->photo=$location;


      if ($user->save()) {
        return response()->json([
          "message"=>"Nama pengguna berhasil di tambahkan",
          "data"=>new UserResource($user)
        ],200);
      }
    }

    public function update(UserReq $request)
    {
      $this->admin = MyLib::admin();

      // $rules = [
      //    'code' => 'required|min:3|exists:\App\Model\User,code',
      //    'password'=>"nullable|min:8",
      //    'confirm_password'=>"required_if:password,present|same:password",
      //    'is_active'=>"boolean",
      //    'role_id'=>"required|exists:roles,id"
      //
      // ];
      //
      // $messages=[
      //    'code.exists' => 'Nama Pengguna tidak terdaftar',
      //    'code.required' => 'Nama Pengguna tidak boleh kosong',
      //    'code.min' => 'Nama pengguna minimal 3 karakter',
      //
      //    // 'password.required' => 'Kata Sandi tidak boleh kosong',
      //    'password.min' => 'Kata Sandi minimal 8 Karakter',
      //
      //    'confirm_password.required_if' => 'Ulang Kata Sandi tidak boleh kosong',
      //    'confirm_password.same' => 'Kata Sandi tidak cocok',
      //    // 'confirm_password.min' => 'Kata Sandi minimal 8 Karakter',
      //
      //    'is_active.boolean' => 'Status Pengguna hanya boleh di pilih',
      //    "role_id.exists"=>"Role tidak terdaftar"
      //
      // ];

      // $validator = \Validator::make($request->all(),$rules,$messages);
      //
      // if ($validator->fails()) {
      //   throw new ValidationException($validator);
      // }

      // $token = $request->bearerToken();
      // if ($token=="") {
      //   throw new MyException("Get user info cannot complete, please refresh your browser then try it again");
      // }
      //
      // $admin = User::where("api_token",$token)->first();
      // if (!$admin) {
      //   throw new MyException("Maaf data yang dimasukkan tidak valid");
      // }

      // $code=$request->code;
      // if ($code=="") {
      //   throw new MyException("Maaf parameter yang dikirimkan tidak sesuai, harap ulangi kembali");
      // }

      $code = $request->code;
      $new_code = $request->new_code;
      $password = $request->password;

      $user = User::where("role_id","!=",0)->where('code',$code)->first();
      if (!$user) {
        throw new MyException("Data Tidak ditemukan");
      }
      $user->admin_code=$this->admin->code;
      $user->role_id=$request->role_id;
      if ($password) {
        $user->password=bcrypt($password);
      }
      if ($new_code && $new_code!=$user->code) {
        $user->code=$new_code;
      }

      $user->is_active=(int)$request->is_active;
      $user->can_login=(int)$request->can_login;
      $user->id_number=$request->id_number;
      $user->name=$request->name;
      $user->birth_date=$request->birth_date;
      $user->address=$request->address;
      $user->gender=$request->gender;
      $user->type=$request->type;


      $old_image = $location = $user->photo;
      $photo_preview = $request->photo_preview;

      $new_image = $request->file('photo');

      if($new_image != null){
        $date=new \DateTime();
        $timestamp=$date->format("Y-m-d H:i:s.v");
        $ext = $new_image->extension();
        $file_name = md5(preg_replace('/( |-|:)/','',$timestamp)).'.'.$ext;
        $location = "/img/employees/{$file_name}";
        ini_set('memory_limit','256M');
        Image::make($new_image)->save(public_path($location));
      }

      if ($new_image == null && $photo_preview == null) {
        $location = null;
      }

      if ($photo_preview==null && $old_image != null && File::exists(public_path($old_image))) {
        unlink(public_path($old_image));
      }

      $user->photo=$location;


      if ($user->save()) {
        return response()->json([
          "message"=>"Data pengguna berhasil di ubah",
          "data"=>new UserResource($user),
          "code"=>$request->code,
          "new_code"=>$request->new_code,
        ],200);
      }
    }

    public function show(Request $request)
    {

      $rules = [
         'code' => 'required|min:3|exists:\App\Model\User,code',
      ];

      $messages=[
         'code.exists' => 'Nama Pengguna tidak terdaftar',
         'code.required' => 'Nama Pengguna tidak boleh kosong',
         'code.min' => 'Nama pengguna minimal 3 karakter',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      // if (!isset($code)) {
      //   throw new MyException("Maaf Data input yang dimasukkan kurang lengkap");
      // }
      //
      // if ($code=="") {
      //   return response()->json([
      //     "data"=>[],
      //   ],200);
      // }

      $data = User::where("code",$request->code)->first();
      if (!$data) {
        throw new MyException("Maaf Data Tidak Ditemukan");
      }
      return response()->json([
        "data"=>new UserResource($data),
      ],200);
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
         'code' => 'required|min:3|exists:\App\Model\User,code',
         'password'=>"required|min:8",
      ];

      $messages=[
         'code.required' => 'Nama Pengguna tidak boleh kosong',
         'code.min' => 'Nama Pengguna minimal 3 karakter',
         'code.exists' => 'Nama Pengguna tidak terdaftar',

         'password.required' => 'Kata sandi tidak boleh kosong',
         'password.min' => 'Kata sandi minimal 8 Karakter',

      ];


      $validator = Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $code = $request->code;
      $password = $request->password;

      $user = User::where("code",$code)->first();
      if (!$user) {
        throw new MyException("Maaf nama pengguna ini tidak terdaftar");
      }
      if ($user->is_active==0) {
        throw new MyException("Maaf Akun anda tidak aktif, hubungi admin");
      }
      if ($user->can_login==0) {
        throw new MyException("Maaf Akun anda tidak diizinkan");
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
      // if ($this->admin->is_active==0) {
      //   return response()->json([
      //     "message"=>"Maaf Akun anda tidak aktif, hubungi admin",
      //   ],400);
      // }

      // if ($admin->can_login == 0) {
      //     return response()->json([
      //       "message"=>"Maaf Akun anda tidak diizinkan",
      //     ],400);
      // }


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
          "code"=>$this->admin->code,
          "scope"=>[$this->admin->role->title]
        ]
      ],200);
    }


    public function change_password(Request $request)
    {
      $this->admin = MyLib::admin();

      $rules = [
         'password'=>"required|min:8",
         'confirm_password'=>"required|min:8|same:password",
      ];

      $messages=[
         'password.required' => 'Kata Sandi tidak boleh kosong',
         'password.min' => 'Kata Sandi minimal 8 Karakter',

         'confirm_password.required' => 'Ulang Kata Sandi tidak boleh kosong',
         'confirm_password.same' => 'Kata Sandi tidak cocok',
         'confirm_password.min' => 'Kata Sandi minimal 8 Karakter',
      ];

      $validator = \Validator::make($request->all(),$rules,$messages);

      if ($validator->fails()) {
        throw new ValidationException($validator);
      }

      $password = $request->password;

      $user = User::where("code",$this->admin->code)->first();
      if (!$user) {
        throw new MyException("Maaf user tidak terdaftar");
      }

      $user->password=bcrypt($password);

      if ($user->save()) {
        return response()->json([
          "message"=>"Password berhasil di ubah",
          // "data"=>new UserResource($user)
        ],200);
      }
    }

    public function notUsedByEmployee(Request $request)
    {

      $this->admin = MyLib::admin();

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
      $users = User::offset($offset)->limit($limit);

      //---- All Developer Hidden
      $users=$users->where("role_id","!=",0);


      //======================================================================================================
      // Model Sorting | Example $request->sort = "code:desc,role:desc";
      //======================================================================================================

      if ($request->sort) {
        $sortList=[];

        $sorts=explode(",",$request->sort);
        foreach ($sorts as $key => $sort) {
          $side = explode(":",$sort);
          $side[1]=isset($side[1])?$side[1]:'ASC';
          $sortList[$side[0]]=$side[1];
        }

        if (isset($sortList["code"])) {
          $users = $users->orderBy("code",$sortList["code"]);
        }

        if (isset($sortList["created_at"])) {
          $users = $users->orderBy("created_at",$sortList["created_at"]);
        }

        if (isset($sortList["updated_at"])) {
          $users = $users->orderBy("updated_at",$sortList["updated_at"]);
        }

        // if (isset($sortList["role"])) {
        //   $users = $users->orderBy(function($q){
        //     $q->from("roles")
        //     ->select("title")
        //     ->whereColumn("id","users.role_id");
        //   },$sortList["role"]);
        // }

        // if (isset($sortList["admin"])) {
        //   $users = $users->orderBy(function($q){
        //     $q->from("users as u")
        //     ->select("u.code")
        //     ->whereColumn("u.id","users.id");
        //   },$sortList["admin"]);
        // }
      }else {
        $users = $users->orderBy('code','ASC');
      }

      // ==============
      // Model Filter
      // ==============

      if (isset($request->code)) {
        $users = $users->where("code",'like','%'.$request->code.'%');
      }
      if (isset($request->role_id)) {
        $users = $users->where("role_id",'like','%'.$request->role_id.'%');
      }
      if (isset($request->admin_code)) {
        $users = $users->where("admin_code",'like','%'.$request->admin_code.'%');
      }

      // $users = $users->where("code",'mal1sss');

      // $user = $users->first();
      // $users=$users->get();

      // $ss = $users->with("employee")->first();
      // return response()->json([
      //   "data"=>$ss->employee,
      // ],200);
      return response()->json([
        "data"=>UserResource::collection($users->doesnthave('employee')->get()),
        // "data"=>UserResource::collection($users)->hide(['is_active']),
        // "data"=>UserResource::make($user)->hide(['image']),

      ],200);
    }

}
