<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <style media="screen">
      @page { margin: 5px 5px 10px 5px; }
      body { margin: 5px 5px 10px 5px; }
    </style>
  </head>
  <body>
     


    {{-- <div class="" style="border:solid 1px #000; width:815px;">

    </div> --}}
    {{-- <div class="" style="width:100%; height:30px; display:inline-block; padding:10px; border-bottom:solid 2px #000;">
      <div class="" style="display:inline-block;">
        <img src="{{ $dt_register->team->logo ?? 'img/main/club.png' }}" alt="" style="max-width:30px; max-height:30px;">
      </div>
      <div style="display:inline-block; font-size:20px;">
        {{ $dt_register->team->name }}
      </div>
    </div> --}}
    {{-- <div class="" style="width:100%; height:20px; display:inline-block; text-align:center; padding:10px; clear:both;">
      OFFICIAL
    </div> --}}
    {{-- <div class="" style="width:100%; display:inline-block; padding:10px; clear:both; margin-bottom:-50px;">
      @foreach ($dt_register->officials as $key => $official)
        @if (($key + 1) <= 6)
          <div class="" style="width:16%; height:150px; float:left; text-align:center;">
            <div class="" style="width:100px; height:100px; margin:auto;">
              <img src="{{ $official->avatar }}" alt="" style="max-width:100px; max-height:100px;">
            </div>
            <div class="" style="width:100%; height:16px; font-size:0.8em; margin-top:10px;">
              {{ $official->date_of_birth }}
            </div>
            <div class="" style="width:100%; height:16px; font-size:0.8em;">
              {{ $official->fullname }}
            </div>
            <div class="" style="width:100%; height:16px; font-size:0.8em; font-weight:bold;">
              {{ $official->role }}
            </div>
          </div>
        @endif
      @endforeach
    </div>
    <div class="" style="width:100%; height:20px; display:inline-block; text-align:center; padding:10px; clear:both;">
      DAFTAR PESERTA
    </div> --}}
    {{-- <div class="" style="width:100%; display:inline-block; padding:10px; clear:both;">
      @foreach ($dt_register->squads as $key => $squad)
        <div class="" style="width:16%; height:145px; float:left; text-align:center; ">
          <div class="" style="width:100px; height:100px; margin:auto;">
            <img src="{{ $squad->avatar }}" alt="" style="max-width:100px; max-height:100px;">
          </div>
          <div class="" style="width:100%; height:16px; font-size:0.8em; margin-top:10px;">
            {{ $squad->date_of_birth }}
          </div>
          <div class="" style="width:100%; height:16px; font-size:0.8em;">
            {{ $squad->fullname }}
          </div>
          <div class="" style="width:100%; height:16px; font-size:0.8em;">
            {{ $squad->back_number }} - {{ $squad->assign }}
          </div>
        </div>
        @if (($key + 1) % 6 == 0)
          <div class="" style="width:100%; height:5px; clear:both; border-top:solid 1px #ccc; "></div>
        @endif
      @endforeach
    </div> --}}


  </body>
</html>
