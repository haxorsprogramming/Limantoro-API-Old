<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectReq extends FormRequest
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
         'type' => 'required|min:3',
         'date'=>['required','date_format:"Y-m-d"'],
         'in_charge_code'=>[
           'required',
           'exists:\App\Model\User,code',
         ],
         'is_finished'=>[
           'required',
           Rule::in(['0', '1']),
         ],

      ];

      if (request()->isMethod('post')) {
          $rules['code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Project,code';
          // $rules['id_number'] = 'required|numeric|unique:App\Model\Project,id_number';
          // $rules['user_code'] = 'nullable|unique:App\Model\Project,user_code';
          // $rules['photo'] = 'required|image|mimes:jpeg|max:2048';
      }
      if (request()->isMethod('put')) {
          $rules['code'] = 'required|min:3|regex:/^\S*$/|exists:\App\Model\Project,code';
          $rules['new_code'] = 'required|min:3|regex:/^\S*$/|unique:\App\Model\Project,code,'.request()->code;
          // $rules['id_number'] = 'required|numeric|unique:App\Model\Project,id_number,'.request()->code;
          // $rules['user_code'] = 'nullable|unique:App\Model\Project,user_code,'.request()->code;
      }
      // if (request()->isMethod('put')) {
      //     $rules['id'] = 'required|int';
      // }

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

          'name.required' => 'Nama Project tidak boleh kosong',
          'name.min' => 'Nama Project minimal 3 karakter',

          'type.required' => 'Jenis tidak boleh kosong',
          'type.min' => 'Jenis minimal 3 karakter',

          'date.required' => 'Tanggal tidak boleh kosong',
          'date.date_format' => 'Format Tanggal Salah',

          'is_finished.required' => 'Status Project tidak boleh kosong',
          'is_finished.in' => 'Data yang di input tidak sesuai',

          'in_charge_code.required' => 'Penanggung Jawab tidak boleh kosong',
          'in_charge_code.exists' => 'Penanggung Jawab tidak terdaftar',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
