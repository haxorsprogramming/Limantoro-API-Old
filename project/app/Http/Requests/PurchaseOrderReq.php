<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderReq extends FormRequest
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
         'purchase_request_number' => 'required|exists:App\Model\PurchaseRequest,number',
         'supplier_code' => 'required|exists:App\Model\Supplier,code',
         'date'=>['required','date_format:"Y-m-d"'],
      ];
      if (request()->isMethod('put')) {
          $rules['number'] = 'required|regex:/^\S*$/|exists:App\Model\PurchaseOrder,number';
      }
      return $rules;
    }

    public function messages()
    {
        return [
          'purchase_request_number.required' => 'PR No. harus di pilih',
          'purchase_request_number.exists' => 'PR No. tidak terdaftar',

          'supplier_code.required' => 'Supplier harus di pilih',
          'supplier_code.exists' => 'Supplier tidak terdaftar',

          'date.required' => 'Tanggal tidak boleh kosong',
          'date.date_format' => 'Format Tanggal Salah',

          'number.required' => 'PO No harus ada',
          'number.exists' => 'PO No tidak terdaftar',
          'number.regex' => 'PO No tidak boleh ada spasi',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
