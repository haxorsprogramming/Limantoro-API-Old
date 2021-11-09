<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseRequestDetailReq extends FormRequest
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
         'qty' => 'required|numeric|min:1',
         'purchase_request_id'=>'required|exists:App\Model\PurchaseRequest,id',
         'product_id'=>'required|exists:App\Model\Product,id'

      ];
      // if (request()->isMethod('put')) {
      //     $rules['id'] = 'required|int';
      // }

      return $rules;
    }

    public function messages()
    {
        return [
          'qty.required' => 'Quantity tidak boleh kosong',
          'qty.numeric' => 'Quantity harus berupa angka',
          'qty.min' => 'Quantity paling sedikit harus 1',


          'purchase_request_id.required' => 'purchase_request_id di perlukan',
          'purchase_request_id.exists' => 'purchase_request_id tidak terdaftar',

          'product_id.required' => 'Produk di perlukan',
          'product_id.exists' => 'Produk tidak terdaftar',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
