<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptOfGoodReq extends FormRequest
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
         'vendor_id' => [
           'required',
           'exists:App\Model\Vendor,id',
         ],

         'project_id' => [
           'required',
           'exists:App\Model\Project,id',
         ],

      ];
      // if (request()->isMethod('put')) {
      //     $rules['id'] = 'required|int';
      // }
      return $rules;
    }

    public function messages()
    {
        return [
          'vendor_id.required' => 'Vendor ID harus di pilih',
          'vendor_id.exists' => 'Vendor ID tidak terdaftar',

          'project_id.required' => 'Project ID harus di pilih',
          'project_id.exists' => 'Project ID tidak terdaftar',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
