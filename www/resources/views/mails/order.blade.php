<div style="text-align:center;max-width: 680px">
	<div style="padding: 30px;background-color:#152532;color:white">
		<h1>MOTORDOCK</h1>
	</div>
	<img src="https://motordock.de/img/check.png">
	<h1>Vielen Dank für Ihre Bestellung!</h1>

	<table width="100%" cellspacing="0" cellpadding="6">
		<caption style="font-weight:bold;font-size:20px">Bestellnummer {{ $num }}</caption>
		<thead>
			<tr style="padding:10px;background-color:#222;color:white">
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
					Total: € {{ $total }} + {{ $deliveryPrice }} = {{ $sum }}
				</th>
			</tr>
		</tfoot>
	</table>
</div>