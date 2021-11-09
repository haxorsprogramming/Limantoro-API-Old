<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProofOfExpenditureReq extends FormRequest
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
         // 'purchase_request_number' => 'required|exists:App\Model\PurchaseRequest,number',
         // 'supplier_code' => 'required|exists:App\Model\Supplier,code',
         'date'=>['required','date_format:"Y-m-d"'],
         'is_paid'=>['required',Rule::in(['0', '1'])],
         'payby'=>['required',Rule::in(['K', 'B','N'])],
         'pay_date'=>['required','date_format:"Y-m-d"'],
         'total_1' => 'nullable|numeric',
         'total_2' => 'nullable|numeric',
      ];
      if (request()->isMethod('put')) {
          $rules['number'] = 'required|regex:/^\S*$/|exists:App\Model\ProofOfExpenditure,number';
      }
      return $rules;
    }

    public function messages()
    {
        return [
          'payby.required' => 'Kas/Bank harus di pilih',
          'payby.in' => 'Kas/Bank hanya boleh di pilih',

          'date.required' => 'Tanggal tidak boleh kosong',
          'date.date_format' => 'Format Tanggal Salah',

          'is_paid.required' => 'Telah Dibayar harus di pilih',
          'is_paid.in' => 'Telah Dibayar hanya boleh di pilih',

          'pay_date.required' => 'Tanggal Bayar tidak boleh kosong',
          'pay_date.date_format' => 'Format Tanggal Bayar Salah',

          'number.required' => 'POE No harus ada',
          'number.exists' => 'POE No tidak terdaftar',
          'number.regex' => 'POE No tidak boleh ada spasi',

          'total_1.numeric' => 'Jumlah Harus Angka',
          'total_2.numeric' => 'Jumlah Harus Angka',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
