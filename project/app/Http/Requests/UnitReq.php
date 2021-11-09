<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitReq extends FormRequest
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
         'name' => 'required|min:3',
         'land_size' => 'required|min:3',
         'building_size' => 'required|min:3',
         'builded' => 'required|numeric',
         'sold' => 'required|numeric',
         'selling_price' => 'required|numeric',
         'marketing_fee' => 'required|numeric',
         'project_id' => 'required|exists:App\Model\Project,id',
         // 'is_finished'=>[
         //   'required',
         //   Rule::in(['0', '1']),
         // ],
      ];
      // if (request()->isMethod('put')) {
      //     $rules['id'] = 'required|int';
      // }
      return $rules;
    }

    public function messages()
    {
        return [
          // 'id.required' => 'ID tidak boleh kosong',
          'name.required' => 'Nama tidak boleh kosong',
          'name.min' => 'Nama minimal 3 karakter',
          'land_size.required' => 'Ukuran tanah tidak boleh kosong',
          'land_size.min' => 'Ukuran tanah minimal 3 karakter',
          'building_size.required' => 'Ukuran banguanan tidak boleh kosong',
          'building_size.min' => 'Ukuran banguanan minimal 3 karakter',

          'builded.required' => 'Jumlah unit di bangun tidak boleh kosong',
          'builded.numeric' => 'Jumlah unit di bangun harus berupa angka',

          'sold.required' => 'Jumlah unit terjual tidak boleh kosong',
          'sold.numeric' => 'Jumlah unit terjual harus berupa angka',

          'selling_price.required' => 'Harga jual tidak boleh kosong',
          'selling_price.numeric' => 'Harga jual harus berupa angka',

          'marketing_fee.required' => 'Marketing fee tidak boleh kosong',
          'marketing_fee.numeric' => 'Marketing fee harus berupa angka',

          'project_id.required' => 'Project tidak boleh kosong',
          'project_id.exists' => 'Project yang di pilih tidak sesuai',


          // 'is_finished.required' => 'Status Project tidak boleh kosong',
          // 'is_finished.in' => 'Data yang di input tidak sesuai',

        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //   return "failed";
    //     $this->validator = $validator;
    // }

}
