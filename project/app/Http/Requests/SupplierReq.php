<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierReq extends FormRequest
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
      $rules = [
         'name' => 'required|min:3',
         'address' => 'required|min:3',
         'city' => 'required|min:3',
         'contact_person' => 'required|min:3',
         'phone_number' => 'required|min:3',
         'npwp' => 'required|min:3',
      ];
      if (request()->isMethod('post')) {
          $rules['code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Supplier,code';
      }
      if (request()->isMethod('put')) {
          $rules['code'] = 'required|min:3|regex:/^\S*$/|exists:\App\Model\Supplier,code';
          $rules['new_code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Supplier,code,'.request()->code;
      }
            return $rules;
    }

    public function messages()
    {
        return [
          'code.required' => 'Kode tidak boleh kosong',
          'code.min' => 'Kode minimal 3 karakter',
          'code.unique' => 'Kode sudah digunakan',
          'code.exists' => 'Kode tidak terdaftar',
          'code.regex' => 'Kode tidak boleh ada spasi',

          'new_code.required' => 'Kode tidak boleh kosong',
          'new_code.min' => 'Kode minimal 3 karakter',
          'new_code.unique' => 'Kode sudah digunakan',
          'new_code.regex' => 'Kode tidak boleh berisi spasi',
          
          'name.required' => 'Nama tidak boleh kosong',
          'name.min' => 'Nama minimal 3 karakter',

          'address.required' => 'Alamat tidak boleh kosong',
          'address.min' => 'Alamat minimal 3 karakter',

          'city.required' => 'Kota tidak boleh kosong',
          'city.min' => 'Kota minimal 3 karakter',

          'contact_person.required' => 'Contact Person tidak boleh kosong',
          'contact_person.min' => 'Contact Person minimal 3 karakter',

          'phone_number.required' => 'Nomor Telepon tidak boleh kosong',
          'phone_number.min' => 'Nomor Telepon minimal 3 karakter',

          'npwp.required' => 'NPWP tidak boleh kosong',
          'npwp.min' => 'NPWP minimal 3 karakter',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
