<form id="sellers" class="card white-bg p-2 text-left mt-10">
	<p class="font-size-30 text my-5 bold">Sellers</p>
	<table width="100%" cellspacing="0" cellpadding="6" class="font-size-13 border-tiny">
			<thead align="center">
				<tr class="dark-bg light-txt">
					<td>ID</td>
					<td>Market</td>
					<th align="left">Store Name</th>
					<th align="left">Seller Name</th>
					<th align="left">Alias</th>
					<td width="26"></td>
				</tr>
			</thead>
			<tbody align="center">
			@foreach($sellers as $seller)
				<tr data-id="{{ $seller->SellerID }}">
					<td>{{ $seller->SellerID }}</td>
					<td>{{ $seller->market }}</td>
					<td align="left">{{ $seller->StoreName }}</td>
					<td align="left">{{ $seller->SellerName }}</td>
					<td align="left" contenteditable="true">{{ $seller->alias }}</td>
					<th
						title="Delete store"
						onclick="deleteRow(this.parentNode, XHR.push({addressee: '/eBay/rm_seller/{{ $seller->SellerID }}'}))"
						class="white-bg cursor-pointer">✕</th>
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
					addressee: "/eBay/sv_seller/"+event.target.parentNode.dataset.id,
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