<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountStore extends FormRequest
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
      // The value is in kilobytes. I.e. max:1024 = max 1 MB.

      return [
        'code' => [
          'required',
          'unique:App\Model\ChartOfAccount,code'
        ],
        'title'=>"required",
        'side'=>[
          'required',
          Rule::in(['L', 'R']),
        ],
        'balance_sheet_group'=>[
          'required',
          Rule::in(['Asset', 'Liability','Equity']),
        ],
      ];
    }

    public function messages()
    {
        return [
          'code.required' => 'Kode tidak boleh kosong',
          'code.unique' => 'Maaf Kode Ini Sudah Terdaftar',

          'title.required' => 'Judul tidak boleh kosong',

          'side.required' => 'Sisi tidak boleh kosong',
          'side.in' => 'Format Sisi Kiri atau Kanan',

          'balance_sheet_group.required' => 'Balance Sheet Group tidak boleh kosong',
          'balance_sheet_group.in' => 'Format Balance Sheet Group Asset , Liability, Equity',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //     $this->validator = $validator;
    // }
}
