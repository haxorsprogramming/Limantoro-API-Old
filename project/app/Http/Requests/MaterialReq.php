<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialReq extends FormRequest
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
         // 'code' => [
         //   'required',
         //   Rule::unique('materials')->ignore($this->code),
         // ],
         'name' => 'required|min:3',
         'satuan' => 'required|min:1',
      ];

      if (request()->isMethod('post')) {
          $rules['code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Material,code';
      }
      if (request()->isMethod('put')) {
          $rules['code'] = 'required|min:3|regex:/^\S*$/|exists:\App\Model\Material,code';
          $rules['new_code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Material,code,'.request()->code;
      }

      return $rules;
    }

    public function messages()
    {
        return [
          'code.required' => 'Kode produk tidak boleh kosong',
          'code.min' => 'Kode produk minimal 3 karakter',
          'code.unique' => 'Kode produk ini sudah digunakan',
          'code.regex' => 'Kode tidak boleh ada spasi',

          'new_code.required' => 'Kode tidak boleh kosong',
          'new_code.min' => 'Kode minimal 3 karakter',
          'new_code.unique' => 'Kode sudah digunakan',
          'new_code.regex' => 'Kode tidak boleh berisi spasi',

          'name.required' => 'Nama produk tidak boleh kosong',
          'name.min' => 'Nama produk minimal 3 karakter',

          'satuan.required' => 'Satuan tidak boleh kosong',
          'satuan.min' => 'Satuan minimal 1 karakter',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
