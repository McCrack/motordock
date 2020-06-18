<div style="max-width: 680px">
	<div style="text-align:center;padding: 10px;background-color:#B0505F;color:white">
		<h1>MOTORDOCK</h1>
		<h3>Rückrufformular</h3>
	</div>
	
	<table width="100%" cellspacing="0" cellpadding="8">
		<tbody>
			<tr align="left">
				<td width="150px">Name:</td>
				<th>{{ $request->name }}</th>
			</tr>
			<tr align="left" bgcolor="#F3F3F3">
				<td>Telefonnummer:</td>
				<th><a href="tel:{{ $request->phone }}">{{ $request->phone }}</a></th>
			</tr>
			<tr align="left">
				<td>Email:</td>
				<th>{{ $request->email }}</th>
			</tr>
		</tbody>
	</table>
	<div style="padding:15px 10px;border:1px solid #CCC;border-radius:3px">
		<h4 style="margin:0">Kommentar</h4>
		<p>
		{{ $request->message }}
		</p>
	</div>
</div>