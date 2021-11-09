<?php
function imgBase64($url)
{
  $public_path = public_path($url);
  $fgc = file_get_contents($public_path);
  $type = pathinfo($fgc, PATHINFO_EXTENSION);

  if ($type == "svg") {
    $base64 = "data:image/svg+xml;base64,".base64_encode($fgc);
  } else {
    $base64 = "data:image/". $type .";base64,".base64_encode($fgc);
  }
  return $base64;
}


function dateFormalID($tanggal)
{
  $source = strtotime($tanggal);
  $day=["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
  $month=["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
  return $day[date("w",$source)]." , ".date("d",$source)." ".$month[date("n",$source)]." ".date("Y",$source);
}

function writeIDFormat($val)
{
  return rtrim(rtrim((string)number_format($val, 2, ",", "."),"0"),",");

  // $string = preg_replace('/[^,0-9]/ig', "", $string);
  // $string = preg_replace(',', ".", $string);
  // $string = preg_replace('/,/ig', "", $string);
  //
  // $splitM =  explode(".",$string);
  // $splitM[0] = preg_replace('/\B(?=(\d{3})+(?!\d))/g', ".", $splitM[0]);
  //
  // if (count($splitM) > 1) {
  //     $splitM[1] = preg_replace('/0+$/', "", $splitM[1]);
  //   }
  //
  // $string = implode(".",$splitM);
  // return $string;
}
