<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptOfGoodDetailReq extends FormRequest
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

        'receipt_of_good_id' => [
          'required',
          'exists:App\Model\ReceiptOfGood,id',
        ],
         'product_id' => [
           'required',
           'exists:App\Model\Product,id',
         ],
         'qty'=>'required|numeric|min:1'
      ];
      // if (request()->isMethod('put')) {
      //     $rules['id'] = 'required|int';
      // }
      return $rules;
    }

    public function messages()
    {
        return [
          'receipt_of_good_id.required' => 'Receipt Of Good ID harus di pilih',
          'receipt_of_good_id.exists' => 'Receipt Of Good ID tidak terdaftar',

          'product_id.required' => 'Product ID harus di pilih',
          'product_id.exists' => 'Product ID tidak terdaftar',

          'qty.required' => 'Qty harus di isi',
          'qty.numeric' => 'Qty tidak berupa angka',
          'qty.min' => 'Qty min 1',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
