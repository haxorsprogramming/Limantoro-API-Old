<table>
  <thead>
    <tr>
      <th>No</th>
      <th>NIK</th>
      <th>Nama</th>
      <th>Tanggal Lahir</th>
      <th>Alamat</th>
      <th>Foto</th>
    </tr>
  </thead>
  <tbody>
    @php $i=1 @endphp
    @foreach($data as $d)
    <tr>
      <td>{{ $i++ }}</td>
      <td>{{$d->id_number}}</td>
      <td>{{$d->name}}</td>
      <td>{{$d->birth_date}}</td>
      <td>{{$d->address}}</td>
      <td>
        @if($d->photo)
          <img src="{{public_path($d->photo)}}" width="60px" height="60px">
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
