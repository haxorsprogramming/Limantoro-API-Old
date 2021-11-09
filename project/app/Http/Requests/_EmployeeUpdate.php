<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class EmployeeUpdate extends FormRequest
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
          'id_number' => [
              'required',
              'numeric',
              Rule::unique('employees')->ignore($this->id),
          ],
          'name'=>"required",
          'birth_date'=>['required','date_format:"Y-m-d"'],
          'address'=>"required",
          'photo'=>"nullable|image|mimes:jpeg|max:2048",
        ];
    }

    public function messages()
    {
        return [
          'id_number.required' => 'Nomor Induk Kependudukan tidak boleh kosong',
          'id_number.numeric' => 'Nomor Induk Kependudukan hanya boleh berisi angka',
          'id_number.unique' => 'Maaf Nomor Induk Kependudukan Ini Sudah Terdaftar',

          'name.required' => 'Nama tidak boleh kosong',

          'birth_date.required' => 'Umur tidak boleh kosong',
          'birth_date.date_format' => 'Format Tanggal Lahir Salah',

          'address.required' => 'Alamat tidak boleh kosong',

          'photo.image' => 'File harus berupa gambar',
          'photo.max' => 'ukuran foto yang di terima maksimal :max kb.',
          'photo.mimes' => 'Format foto harus :values.',
        ];
    }

    // public $validator = null;
    // protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    // {
    //     $this->validator = $validator;
    // }
}
