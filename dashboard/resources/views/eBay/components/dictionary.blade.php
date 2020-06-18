<form class="card white-bg mt-10 p-2" autocomplete="off">
	<p class="mb-5 mt-0 font-size-20 text-bold">Dictionary</p>
	<table width="100%" cellspacing="0" cellpadding="6" class="font-size-13 border-tiny">
		<thead>
			<tr class="dark-bg light-txt">
				<td width="26"></td>
				<th>Key</th>
				<th>Translate</th>
				<th width="40">Sort id</th>
				<td width="26"></td>
			</tr>
		</thead>
		<tbody>
			@foreach($dictionary->toArray() as $row)
			<tr data-key="{{ $row['word'] }}">
				<th class="white-bg cursor-pointer" title="Add row" align="center" onclick="addRow(this.parentNode)">+</th>
				@foreach($row as $field => $val)
				<td contenteditable="true" data-field="{{ $field }}">{{ $val }}</td>
				@endforeach
				<th class="white-bg cursor-pointer" title="Delete row" align="center" onclick="deleteRow(this.parentNode)">✕</th>
			</tr>
			@endforeach
		</tbody>
	</table>
	<script>
	(function(form){
		var timeout;
		form.oninput = function(event){
			clearTimeout(timeout);
			timeout = setTimeout(function(){
				XHR.json({
					addressee: "/eBay/ch_dictionary",
					body:{ alias: event.target.textContent.trim() },
					onsuccess: function(response){
						if(parseInt(response)){
							SaveIndicator.checked = false;
						}
					}
				});
			},2000);
			SaveIndicator.checked = true;
		}
	})(document.currentScript.parentNode)
	</script>
</form>