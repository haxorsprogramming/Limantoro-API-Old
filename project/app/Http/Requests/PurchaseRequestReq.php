<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequestReq extends FormRequest
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
         'project_code' => 'required|exists:App\Model\Project,code',
         'date'=>['required','date_format:"Y-m-d"'],
      ];
      if (request()->isMethod('put')) {
          $rules['number'] = 'required|regex:/^\S*$/|exists:App\Model\PurchaseRequest,number';
      }
      return $rules;
    }

    public function messages()
    {
        return [
          'project_code.required' => 'Project harus di pilih',
          'project_code.exists' => 'Project tidak terdaftar',

          'date.required' => 'Tanggal tidak boleh kosong',
          'date.date_format' => 'Format Tanggal Salah',

          'number.required' => 'PR No harus ada',
          'number.exists' => 'PR No tidak terdaftar',
          'number.regex' => 'PR No tidak boleh ada spasi',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
