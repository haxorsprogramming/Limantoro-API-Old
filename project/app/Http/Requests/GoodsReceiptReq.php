<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoodsReceiptReq extends FormRequest
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
         'purchase_order_number' => 'required|exists:App\Model\PurchaseOrder,number',
         'supplier_code' => 'required|exists:App\Model\Supplier,code',
         'date'=>['required','date_format:"Y-m-d"'],
         'delivery_order_letter_number'=>['required'],

      ];
      // if (request()->isMethod('post')) {
      //     $rules['number'] = 'required|regex:/^\S*$/|unique:App\Model\GoodsReceipt,number';
      // }
      if (request()->isMethod('put')) {
          $rules['number'] = 'required|regex:/^\S*$/|exists:App\Model\GoodsReceipt,number';
      }
      return $rules;
    }

    public function messages()
    {
        return [
          'purchase_order_number.required' => 'PO No. harus di pilih',
          'purchase_order_number.exists' => 'PO No. tidak terdaftar',

          'supplier_code.required' => 'Supplier harus di pilih',
          'supplier_code.exists' => 'Supplier tidak terdaftar',

          'date.required' => 'Tanggal tidak boleh kosong',
          'date.date_format' => 'Format Tanggal Salah',

          'number.required' => 'GR No harus ada',
          'number.exists' => 'GR No tidak terdaftar',
          'number.regex' => 'GR No tidak boleh ada spasi',

          'delivery_order_letter_number.required' => 'No Surat Jalan tidak boleh kosong',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
