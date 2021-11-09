<!DOCTYPE html>
<html>
<head>
	<title>Laporan Persetujuan Permintaan Pembelian</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	{{--<link rel="stylesheet" href="{{ public_path('bootstrap.min.css') }}" type="text/css" >--}}
	{{--<link rel="stylesheet" href="{{ ltrim(public_path('bootstrap.min.css'), '/') }}" type="text/css" >--}}
	<style type="text/css">
	 @page { margin: 120px 50px 50px 50px; }

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
				width:100%; border-bottom:solid 1px black;
			}

			header .tbl-name{
				text-align:left; font-weight:bold; font-size:13px;
			}

			header .tbl-title{
				vertical-align:bottom; text-align:right; font-weight:bold; font-size:20px;
			}

			.txt-left{ text-align:left; }
			.txt-right{ text-align:right; }

			.bold{
				font-weight: bold;
			}

			.two_dot{
				width:20px;
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

			table.list tbody tr td{
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
				<td rowspan="3" class="tbl-title"> Persetujuan Permintaan Pembelian </td>
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
				<td class="bold txt-left" style="width:50px;">Project</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$pr->project->name}}</td>
				<td class="bold txt-left" style="width:115px;">No. PR</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$pr->number}}</td>
				<td class="bold txt-left" style="width:115px;">Tanggal Permintaan</td>
				<td class="two_dot">:</td>
				<td class="txt-right" style="width:160px;">{{dateFormalID($pr->date)}}</td>
			</tr>
			<tr>
				<td class="bold txt-left">Alamat</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$pr->project->address}}</td>
				<td class="bold txt-left">Penanggung Jawab</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$pr->project->in_charge->name}}</td>
			</tr>
		</table>

		<table class='table-bordered table-striped list'>
			<thead>
				<tr>
					<th>No</th>
					<th>Kode Material</th>
					<th>Nama Material</th>
					<th>Satuan</th>
					<th>Qty dipesan</th>
					<th>Qty disetujui</th>
					<th>Pesan</th>
				</tr>
			</thead>
			<tbody>
				@php $i=1 @endphp
				@foreach($pr->purchase_request_details as $purchase_request_detail)
				<tr>
					<td>{{ $i++ }}</td>
					<td>{{$purchase_request_detail->material->code}}</td>
					<td>{{$purchase_request_detail->material->name}}</td>
					<td>{{$purchase_request_detail->material->satuan}}</td>
					<td>{{writeIDFormat($purchase_request_detail->requested_qty)}}</td>
					<td>{{writeIDFormat($purchase_request_detail->approved_qty)}}</td>
					<td>{{$purchase_request_detail->note}}</td>
				</tr>
				@endforeach
			</tbody>
			{{--<tfoot>
		    <tr>
		      <td>Sum</td>
		      <td>$180</td>
		    </tr>
		  </tfoot>--}}
		</table>

		<table style="width:100%; margin-top:15px;">
			<tr>
				<td>
						<div class="" style="width:150px; float:right;">
								Di Setujui Oleh
								<br>
								<br>
								<br>
								<br>
								<br>
								{{ $pr->approver->name }}
						</div>
				</td>
			</tr>
		</table>
	</main>
</body>

</html>
