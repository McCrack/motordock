<div style="max-width: 680px">
	<div style="text-align:center;padding: 10px;background-color:#152532;color:white">
		<h1>MOTORDOCK</h1>
		<h3>Bestellnummer {{ $num }}</h3>
	</div>
	
	<table width="100%" cellspacing="0" cellpadding="8">
		<tbody>
			<tr align="left" bgcolor="#F3F3F3">
				<td width="150px">Name:</td>
				<th>{{ $citizen->name }} {{ $citizen->last_name }}</th>
			</tr>
			<tr align="left">
				<td>Telefonnummer:</td>
				<th><a href="tel:{{ $citizen->phone }}">{{ $citizen->phone }}</a></th>
			</tr>
			<tr align="left" bgcolor="#F3F3F3">
				<td>Email:</td>
				<th>{{ $citizen->email }}</th>
			</tr>
			<tr align="left">
				<td>Ansehen:</td>
				<th>{{ $citizen->reputation }}</th>
			</tr>
			<tr align="left" bgcolor="black" style="color:white">
				<td colspan="2">Lieferadresse</td>
			</tr>
			<tr align="left">
				<td>Postleitzahl:</td>
				<th>{{ $request->postcode }}</th>
			</tr>
			<tr align="left" bgcolor="#F3F3F3">
				<td>Stadt:</td>
				<th>{{ $request->city }}</th>
			</tr>
			<tr align="left">
				<td style="white-space:nowrap">Straße und Hausnummer:</td>
				<th>{{ $request->address }}</th>
			</tr>
		</tbody>
	</table>
	<div style="padding:15px 10px;border:1px solid #CCC;border-radius:3px">
		<h4 style="margin:0">Kommentar zur Bestellung</h4>
		<p>
		{{ $request->message }}
		</p>
	</div>
	<br>
	<table width="100%" cellspacing="0" cellpadding="6">
		<thead>
			<tr bgcolor="#222222" style="padding:10px;color:white">
				<th align="left">Bezeichnung</th>
				<th align="right">Preis</th>
			</tr>
		</thead>
		<tbody>
			@foreach($items as $item)
			<tr>
				<td align="left">{{ $item->named }}</td>
				<th align="right" style="white-space:nowrap">€ {{ $item->price }}</th>
			</tr>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<th align="right" colspan="2" style="border-top:1px solid #CCC">
					Total: € {{ $total }}
				</th>
			</tr>
		</tfoot>
	</table>
</div>