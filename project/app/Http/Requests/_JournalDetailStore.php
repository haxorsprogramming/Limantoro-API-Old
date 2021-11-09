<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalDetailStore extends FormRequest
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
        'debit'=>"required|numeric",
        'credit'=>"required|numeric",
      ];
    }

    public function messages()
    {
        return [
          'debit.required' => 'Nilai Debit harus diisi',
          'debit.numeric' => 'Nilai Debit harus berupa bilangan positif ataupun negative',
          'credit.required' => 'Nilai Credit harus diisi',
          'credit.numeric' => 'Nilai Credit harus berupa bilangan positif ataupun negative',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //     $this->validator = $validator;
    // }
}
