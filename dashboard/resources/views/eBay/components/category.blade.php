<form class="card white-bg mt-10 flex-md justify-around align-items-stretch" autocomplete="off">
	<div class="py-2">
		<fieldset class="float-left font-size-13 border-none">
			<legend>ID</legend>
			<input name="cat_id" class="field field-size-60 font-size-13" value="{{ $category->id }}" readonly required>
		</fieldset>
		<fieldset class="float-right font-size-13 border-none">
			<legend>Parent ID</legend>
			<input name="parent_id" class="field field-size-60 font-size-13" value="{{ $category->parent_id }}" required>
		</fieldset>
		<fieldset class="clear font-size-13 border-none">
			<legend>Slug</legend>
			<input name="slug" class="field field-size-200" value="{{ $category->slug }}" placeholder="..." required>
		</fieldset>
			<fieldset class="float-left font-size-13 border-none">
			<legend>Delivery Price</legend>
			<input name="delivery_price" class="field field-size-100" value="{{ $category->delivery_price }}" placeholder="..." required>
		</fieldset>
		<fieldset class="float-left font-size-13 border-none">
			<legend>Status</legend>
			<select name="status" class="field">
				@foreach([
					"enabled",
					"disabled"
				] as $status)
				<option @if($status == $category->status) selected @endif value="{{ $status }}">{{ $status }}</option>
				@endforeach
			</select>
		</fieldset>
	</div>
	<div class="w-50 py-2">
		<p class="mb-5 mt-0 font-size-20 text-bold">Category Name</p>
		<table width="100%" cellspacing="0" cellpadding="6" class="font-size-13 border-tiny">
			<thead>
				<tr class="dark-bg light-txt">
					<td width="26"></td>
					<th align="center" width="60">Lang</th>
					<th>Translate</th>
					<td width="26"></td>
				</tr>
			</thead>
			<tbody>
			@foreach($category->name as $lang=>$translate)
				<tr>
					<th class="white-bg cursor-pointer" title="Add row" align="center" onclick="addRow(this.parentNode)">+</th>
					<td align="center">{{ $lang }}</td>
					<td contenteditable="true">{{ $translate }}</td>
					<th class="white-bg cursor-pointer" title="Delete row" align="center" onclick="deleteRow(this.parentNode)">✕</th>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
	<script>
	(function(form){
		var timeout;
		form.oninput = function(event){
			clearTimeout(timeout);
			timeout = setTimeout(function(){
				var field = {};
				if(event.target.nodeName == "TD"){
					field['name'] = (function(langs, key){
						form.querySelectorAll("table > tbody > tr > td").forEach(function(cell, i){
							if(i%2 && key){
								langs[key] = cell.textContent.trim();
							}else if(cell.textContent.length) {
								key = cell.textContent.trim();
							}
						});
						return langs
					})({})
				}else field[event.target.name] = event.target.value;
				form.save(field);
			},3000);
			SaveIndicator.checked = true;
		}
		form.onchange = function(event){
			if(SaveIndicator.checked){
				var field = {};
				field[event.target.name] = event.target.value;
				form.save(field);
			}
		}
		form.save = function(field){
			XHR.json({
				addressee: "/eBay/sv_category/"+form.cat_id.value,
				body: field,
				onsuccess: function(response){
					SaveIndicator.checked = false;
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</form>