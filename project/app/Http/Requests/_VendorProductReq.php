<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorProductReq extends FormRequest
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
         'product_id'=>'required|exists:App\Model\Product,id',
         // 'price' => 'required|numeric',
         'vendor_id'=>'required|exists:App\Model\Vendor,id',
      ];
      return $rules;
    }

    public function messages()
    {
        return [
          'product_id.required' => 'Produk tidak boleh kosong',
          'product_id.exists' => 'Produk tidak terdaftar',

          // 'price.required' => 'Harga tidak boleh kosong',
          // 'price.numeric' => 'Harga harus berupa angka',

          'vendor_id.required' => 'Vendor harus diisi',
          'vendor_id.exists' => 'Vendor tidak terdaftar',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
