<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalDetailReq extends FormRequest
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
        'debit'=>"required|numeric",
        'credit'=>"required|numeric",
      ];

      if (request()->isMethod('put')) {
          $rules['id'] = 'required|int';
      }

      return $rules;
    }

    public function messages()
    {
        return [
          'id.required' => 'ID tidak boleh kosong',

          'debit.required' => 'Nilai Debit harus diisi',
          'debit.numeric' => 'Nilai Debit harus berupa bilangan positif ataupun negative',

          'credit.required' => 'Nilai Credit harus diisi',
          'credit.numeric' => 'Nilai Credit harus berupa bilangan positif ataupun negative',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
