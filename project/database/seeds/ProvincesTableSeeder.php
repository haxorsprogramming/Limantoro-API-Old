<?php

use Illuminate\Database\Seeder;

class ProvincesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $url_province = "https://api.rajaongkir.com/starter/province?key=513ba350529527ee154ba1bff48fdb4b";
      $json_str = file_get_contents($url_province);
      $json_obj = json_decode($json_str);
      $provinces = [];
      foreach($json_obj->rajaongkir->results as $province){
        $provinces[] = [
          'id' => $province->province_id,
          'name' => $province->province,
          'updated_at'=>date("y-m-d H:i:s")
        ];
      }
      DB::table('provinces')->insert($provinces);
    }
}
