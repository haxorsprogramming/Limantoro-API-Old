<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalDetailStoreMsg extends FormRequest
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
        'journal_id' =>  'required|exists:App\Model\Journal,id',
        'chart_of_account_id'=>"required|exists:App\Model\ChartOfAccount,id",
      ];
    }

    public function messages()
    {
        return [
          'journal_id.required' => 'Data input tidak lengkap',
          'journal_id.exists' => 'Data input tidak lengkap',
          'chart_of_account_id.required' => 'Data input tidak lengkap',
          'chart_of_account_id.exists' => 'Data input tidak lengkap',
        ];
    }

    public $validator = null;
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->validator = $validator;
    }
}
