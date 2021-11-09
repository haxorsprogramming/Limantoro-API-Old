<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequestDetailOrderReq extends FormRequest
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
         'qty'=>'required|numeric|min:1',
         'purchase_request_detail_id' => 'required|exists:App\Model\PurchaseRequestDetail,id',
         'vendor_id' => 'required|exists:App\Model\Vendor,id',
         'product_id' => 'required|exists:App\Model\Product,id',

      ];
      // if (request()->isMethod('put')) {
      //     $rules['id'] = 'required|int';
      // }
      return $rules;
    }

    public function messages()
    {
        return [
          'purchase_request_detail_id.required' => 'Purchase Request Detail ID harus di pilih',
          'purchase_request_detail_id.exists' => 'Purchase Request Detail ID tidak terdaftar',

          'vendor_id.required' => 'Vendor harus di pilih',
          'vendor_id.exists' => 'Vendor tidak terdaftar',

          'product_id.required' => 'Produk harus di pilih',
          'product_id.exists' => 'Produk tidak terdaftar',

          'qty.required' => 'Qty harus di isi',
          'qty.numeric' => 'Qty harus angka',
          'qty.min' => 'Qty Minimal 1',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
