<!DOCTYPE html>
<html>
<head>
	<title>Laporan Project</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<style type="text/css">
	 @page { margin: 120px 10px 50px 10px; }

		table tr td,
		table tr th{
			font-size: 9pt;
			text-align: center;
		}
		/** Define the header rules **/
      header {
          position: fixed;
          top: -100px;
          left: 0px;
          /* right: 0cm; */
          height: 80px;

          /** Extra personal styles **/
          /* background-color: #03a9f4; */
          color: black;
          /* text-align: center;
          line-height: 1.5cm; */
					/* display:flex; flex-flow:wrap; align-items:center; */
					width:100%;
					border-bottom:solid 1px black;
					padding-bottom: 10px;
      }

      /** Define the footer rules **/
      footer {
          position: fixed;
          bottom: 0cm;
          left: 0cm;
          right: 0cm;
          height: 2cm;

          /** Extra personal styles **/
          background-color: #03a9f4;
          color: white;
          text-align: center;
          line-height: 1.5cm;
      }

			header .tbl{
				width:100%; ;
			}

			header .tbl-name{
				text-align:left; font-weight:bold; font-size:13px;
			}

			header .tbl-title{
				vertical-align:bottom; text-align:right; font-weight:bold; font-size:35px;
			}

			.txt-left{ text-align:left; }
			.txt-right{ text-align:right; }

			.bold{
				font-weight: bold;
			}

			.two_dot{
				width:10px;
			}

			table.list{
				width:100%;
			  margin-top:20px;
			}

			table.list thead tr{
				background:#7D7D7D;
			}

			table.list thead tr th{
				padding:5px 5px;
				font-size: 14px;
			}

			table.list tbody tr td,table.list tfoot tr td{
				padding:5px 5px;
			}

	</style>
</head>
<body>

	<header style="">
		<table class="tbl">
			<tr>
				<td rowspan="3" style="width:73.71px;">
					<img src="{{ imgBase64($company['logo']) }}" style="max-width:73.71px; max-height:80px;">
				</td>
				<td class="tbl-name">{{ $company['name'] }}</td>
				<td rowspan="3" class="tbl-title">  </td>
			</tr>
			<tr>
				<td class="txt-left">{{ $company['address'] }}</td>
			</tr>
			<tr>
				<td class="txt-left">{{ $company['phone_number'] }}</td>
			</tr>
		</table>
	</header>

	 <!-- <footer>
			 Copyright &copy; <?php echo date("Y");?>
	 </footer> -->
	 <main>

		<center>


		</center>

		<table style="width:100%;">
			<tr>
			<td class="bold txt-left" style="width:75px;">Kode Project</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data->code}}</td>
				<td class="bold txt-left" style="width:100px;">Nama Project</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data->name}}</td>

				<td class="bold txt-left" style="width:95px;">Tanggal Project</td>
				<td class="two_dot">:</td>
				<td class="txt-right" style="width:160px;">{{dateFormalID($data->date)}}</td>
			</tr>
			<tr>
				<td class="bold txt-left">Type Project</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data->type}}</td>
				<td class="bold txt-left">Alamat Project</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data->address}}</td>
				<td class="bold txt-left">Penanggung Jawab</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data->in_charge->name}}</td>
			</tr>
		</table>


		<div style="margin-top:10px;">
			<span style="font-weight:bold; font-size:12px;"> Material Dari Stock </span>
			<table class='table-bordered table-striped list' style="margin-top:0px;">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode Material</th>
						<th>Nama Material</th>
						<th>Satuan</th>
						<th>Qty</th>
					</tr>
				</thead>
				<tbody>
					@php $i=1 @endphp
					@foreach($inject_mats as $im)
					<tr>
						<td>{{ $i++ }}</td>
						<td>{{$im->material->code}}</td>
						<td>{{$im->material->name}}</td>
						<td>{{$im->material->satuan}}</td>
						<td>{{writeIDFormat($im->qty)}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>


		@foreach($sup_grup as $sg)
		<div style="margin-top:10px;">
			<span style="font-weight:bold; font-size:12px;"> Supplier : </span><span style="font-size:12px; margin:0px;"> {{$sg['supplier']['code']}}/{{$sg['supplier']['name']}}</span>
			<table class='table-bordered table-striped list' style="margin-top:0px;">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode Material</th>
						<th>Nama Material</th>
						<th>Satuan</th>
						<th>Qty dipesan</th>
						<th>Qty diretur</th>
						<th>Jumlah</th>
					</tr>
				</thead>
				<tbody>
					@php $i=1 @endphp
					@foreach($sg['materials'] as $mts)
					<tr>
						<td>{{ $i++ }}</td>
						<td>{{$mts['code']}}</td>
						<td>{{$mts['name']}}</td>
						<td>{{$mts['satuan']}}</td>
						<td>{{writeIDFormat($mts['qty'])}}</td>
						<td>{{writeIDFormat($mts['qty_return'])}}</td>
						<td>{{writeIDFormat($mts['total'])}}</td>
					</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6" style="font-size:14px; font-weight:bold;"> Total </td>
						<td>{{writeIDFormat($sg['total'])}}</td>
					</tr>
				</tfoot>

			</table>
		</div>
		@endforeach

		<div style="margin-top:10px;">
			<span style="font-weight:bold; font-size:12px;"> Total Material </span>
			<table class='table-bordered table-striped list' style="margin-top:0px;">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode Material</th>
						<th>Nama Material</th>
						<th>Satuan</th>
						<th>Qty</th>
					</tr>
				</thead>
				<tbody>
					@php $i=1 @endphp
					@foreach($mat_grup as $mg)
					<tr>
						<td>{{ $i++ }}</td>
						<td>{{$mg['code']}}</td>
						<td>{{$mg['name']}}</td>
						<td>{{$mg['satuan']}}</td>
						<td>{{writeIDFormat($mg['qty'])}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<div style="margin-top:10px;">
			<span style="font-weight:bold; font-size:12px;"> Material Tersisa </span>
			<table class='table-bordered table-striped list' style="margin-top:0px;">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode Material</th>
						<th>Nama Material</th>
						<th>Satuan</th>
						<th>Qty</th>
					</tr>
				</thead>
				<tbody>
					@php $i=1 @endphp
					@foreach($rest_mats as $im)
					<tr>
						<td>{{ $i++ }}</td>
						<td>{{$im->material->code}}</td>
						<td>{{$im->material->name}}</td>
						<td>{{$im->material->satuan}}</td>
						<td>{{writeIDFormat($im->qty)}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

	</main>
</body>

</html>
