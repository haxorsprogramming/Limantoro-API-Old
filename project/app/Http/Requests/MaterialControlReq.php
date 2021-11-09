<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialControlReq extends FormRequest
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
         // 'date'=>['required','date_format:"Y-m-d"'],
         'status'=>['required',Rule::in(['Inject', 'Rest'])],
      ];
      // if (request()->isMethod('put')) {
      //     $rules['number'] = 'required|regex:/^\S*$/|exists:App\Model\MaterialControl,number';
      // }
      return $rules;
    }

    public function messages()
    {
        return [
          'project_code.required' => 'Project harus di pilih',
          'project_code.exists' => 'Project tidak terdaftar',

          'status.required' => 'Status harus ada',
          'status.in' => 'Format Tidak Cocok',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
