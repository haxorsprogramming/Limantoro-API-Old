<!DOCTYPE html>
<html>
<head>
	<title>Laporan Transaksi</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	{{-- <link rel="stylesheet" href="{{ asset('/lib/bootstrap4.3.1/css/bootstrap.min.css') }}"> --}}
</head>
<body>
	<style type="text/css">
		table tr td,
		table tr th{
			font-size: 9pt;
			text-align: center;
		}
	</style>
	<center>
		<h5>Laporan Transaksi Periode </h4>
		{{-- <h6><a target="_blank" href="https://www.malasngoding.com/membuat-laporan-…n-dompdf-laravel/">www.malasngoding.com</a></h5> --}}
	</center>

	<table class='table-bordered table-striped'>
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
						<img src="{{public_path($d->photo)}}" style="max-width:60px; max-height:60px;">
					@endif
				</td>
			</tr>
			@endforeach
		</tbody>

	</table>

</body>
</html>
