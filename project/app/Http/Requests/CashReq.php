<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashReq extends FormRequest
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
          'no_acc' => 'required|min:3',
       ];
       if (request()->isMethod('post')) {
           $rules['code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Cash,code';
       }
       if (request()->isMethod('put')) {
           $rules['code'] = 'required|min:3|regex:/^\S*$/|exists:\App\Model\Cash,code';
           $rules['new_code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Cash,code,'.request()->code;
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

           'no_acc.required' => 'Kota tidak boleh kosong',
           'no_acc.min' => 'Kota minimal 3 karakter',
         ];
     }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
