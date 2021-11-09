<!DOCTYPE html>
<html>
<head>
	<title>Laporan Bukti Keluar</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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
				vertical-align:bottom; text-align:right; font-weight:bold; font-size:35px;
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

			table.list tfoot tr td{
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
				<td rowspan="3" class="tbl-title"> Bukti Keluar </td>
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
				<td class="bold txt-left" style="width:115px;">No. POE</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data['number']}}</td>
				<td class="bold txt-left" style="width:115px;">Tanggal</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{dateFormalID($data['date'])}}</td>
				<td class="bold txt-left" style="width:115px;">Tanggal Dibayar</td>
				<td class="two_dot">:</td>
				<td class="txt-right" style="width:160px;">{{dateFormalID($data['pay_date'])}}</td>
			</tr>
			<tr>
				<td class="bold txt-left">Catatan</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data['note']}}</td>
				<td class="bold txt-left">Telah Di Bayar?</td>
				<td class="two_dot">:</td>
				<td class="txt-left">{{$data['is_paid'] ? 'Sudah Dibayar' : 'Belum Dibayar'}}</td>
			</tr>
		</table>

		<table class='table-bordered table-striped list'>
			<thead>
				<tr>
					<th>No</th>
					<th>Uraian</th>
					<th>Jumlah</th>
				</tr>
			</thead>
			<tbody>
				@php $i=1 @endphp
				@foreach($data['proof_of_expenditure_details'] as $proof_of_expenditure_detail)
				<tr>
					<td>{{ $i++ }}</td>
					<td>{{ $proof_of_expenditure_detail['description'] }}</td>
					@if($proof_of_expenditure_detail['total'] < 0)
					<td style="width:100px;">( {{ writeIDFormat($proof_of_expenditure_detail['total']*-1) }} )</td>
					@else
					<td style="width:100px;">{{ writeIDFormat($proof_of_expenditure_detail['total']) }}</td>
					@endif
				</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2" style="text-align:right; font-weight:bold; font-size:16px;">Tagihan</td>
					<td style="width:100px;">{{writeIDFormat($data["total"])}}</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:right; font-weight:bold; font-size:16px;">Diskon</td>
					<td style="width:100px;">{{writeIDFormat($data["discount"])}}</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:right; font-weight:bold; font-size:16px;">Total Tagihan</td>
					<td style="width:100px;">{{writeIDFormat($data["total"] - $data["discount"])}}</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:right; font-weight:bold; font-size:16px;"><span style="font-size:12px; font-weight:normal;"> ( {{ $data['bank_1'] }} {{ $data['check_number_1'] ? (', '.$data['check_number_1']) : '' }} ) </span> Pembayaran 1</td>
					<td style="width:100px;">{{writeIDFormat($data["total_1"] ?? 0)}}</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:right; font-weight:bold; font-size:16px;"><span style="font-size:12px; font-weight:normal;"> ( {{ $data['bank_2'] }} {{ $data['check_number_2'] ? (', '.$data['check_number_2']) : '' }} ) </span> Pembayaran 2</td>
					<td style="width:100px;">{{writeIDFormat($data["total_2"] ?? 0)}}</td>
				</tr>
			</tfoot>
		</table>

		<table style="width:100%; margin-top:15px;">
			<tr>
				<td></td>
				<td style="width:150px; text-align:center;">
					Dibuat Oleh
					<br>
					<br>
					<br>
					<br>
					<br>
					{{ $data['admin']['name'] }}
				</td>
				<td style="width:150px; text-align:center;">
					Disetujui Oleh
					<br>
					<br>
					<br>
					<br>
					<br>
					{{$owner->name}}
				</td>
			</tr>
		</table>

	</main>
</body>

</html>
