<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class UserReq extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
      // The value is in kilobytes. I.e. max:1024 = max 1 MB.

        $rules = [
          'name'=>"required",
          'birth_date'=>['nullable','date_format:"Y-m-d"'],
          'photo'=>"nullable|image|mimes:jpeg|max:2048",
          'is_active'=>['nullable', Rule::in(['0', '1'])],
          'can_login'=>['nullable', Rule::in(['0', '1'])],
          'password'=>"nullable|min:8",
          'confirm_password'=>["required_with:password","nullable","min:8","same:password"],
          'role_id'=>"required|exists:roles,id"
        ];
        if (request()->isMethod('post')) {
            $rules['code'] = ['required','min:3','regex:/^\S*$/','unique:\App\Model\User,code'];
            $rules['id_number'] = 'nullable|numeric|unique:App\Model\User,id_number';
            // $rules['user_code'] = 'nullable|unique:App\Model\User,user_code';
            // $rules['photo'] = 'required|image|mimes:jpeg|max:2048';
        }
        if (request()->isMethod('put')) {
            $rules['code'] = ['required','min:3','regex:/^\S*$/','exists:\App\Model\User,code'];
            $rules['new_code'] = ['required','min:3','regex:/^\S*$/','unique:\App\Model\User,code,'.request()->code];
            $rules['id_number'] = 'nullable|numeric|unique:App\Model\User,id_number,'.request()->code;
            // $rules['user_code'] = 'nullable|unique:App\Model\User,user_code,'.request()->code;
        }

        return $rules;

    }

    public function messages()
    {
        return [
          // 'id_number.required' => 'ID Number tidak boleh kosong',
          // 'id_number.numeric' => 'ID Number numeric',
          // 'id_number.unique' => 'ID Number sudah digunakan',

          'code.required' => 'Kode tidak boleh kosong',
          'code.min' => 'Kode minimal 3 karakter',
          'code.unique' => 'Kode sudah digunakan',
          'code.exists' => 'Kode tidak terdaftar',
          'code.regex' => 'Kode tidak boleh berisi spasi',

          'new_code.required' => 'Kode tidak boleh kosong',
          'new_code.min' => 'Kode minimal 3 karakter',
          'new_code.unique' => 'Kode sudah digunakan',
          'new_code.regex' => 'Kode tidak boleh berisi spasi',

          'id_number.required' => 'Nomor Induk Kependudukan tidak boleh kosong',
          'id_number.numeric' => 'Nomor Induk Kependudukan hanya boleh berisi angka',
          'id_number.unique' => 'Maaf Nomor Induk Kependudukan Ini Sudah Terdaftar',

          'name.required' => 'Nama tidak boleh kosong',

          // 'birth_date.required' => 'Tanggal Lahir tidak boleh kosong',
          'birth_date.date_format' => 'Format Tanggal Lahir Salah',

          'address.required' => 'Alamat tidak boleh kosong',

          // 'photo.required' => 'Pilih foto terlebih dahulu',
          'photo.image' => 'File harus berupa gambar',
          'photo.max' => 'ukuran foto yang di terima maksimal :max kb.',
          'photo.mimes' => 'Format foto harus :values.',

          'user_code.unique' => 'User sudah digunakan',
          'is_active.in' => 'Pengguna Aktif harus dipilih',
          'can_login.in' => 'Bisa Login harus dipilih',
          'password.min' => 'Kata Sandi minimal 8 Karakter',

          'confirm_password.required_with' => 'Ulang Kata Sandi tidak boleh kosong',
          'confirm_password.same' => 'Kata Sandi tidak cocok',
          'confirm_password.min' => 'Kata Sandi minimal 8 Karakter',

          'role_id.required' => 'Jabatan tidak boleh kosong',
          "role_id.exists"=>"Jabatan tidak terdaftar"
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //     $this->validator = $validator;
    // }
}
