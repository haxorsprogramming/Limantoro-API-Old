<?php
namespace App\Exports;
use App\Model\Transaction;
// ​
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;


class EmployeeReport implements FromView
{
   use Exportable;
   public $data;

   public function __construct($data)
   {
      $this->data = $data;
   }

   // public function index($value)
   // {
   //    return $value;
   // }
   //
   public function view(): View
    {
        return view('laporan.excel', [
            'data' => $this->data
        ]);
    }

    // public function collection()
    // {
    //     return Transaction::all();
    // }
}
?>
