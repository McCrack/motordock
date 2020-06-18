<form id="search" class="card white-bg p-2 text-left mt-10">
	<fieldset class="float-right border-none pl-0 font-size-13 mt-10">
		<legend class="p-0">
			<label><input type="checkbox" name="pricelimits" class="ml-0" autocomplete="off"> Limits of Price</label>
		</legend>
		<input type="number" name="min_price" value="{{ $cng->{'Min Price'} }}" min="1" class="field field-size-100" title="Min Price">
		<span class="mx-10">to</span>
		<input type="number" name="max_price" value="{{ $cng->{'Max Price'} }}" min="2" class="field field-size-100" title="Max Price">
	</fieldset>

	<fieldset class="border-none pl-0 font-size-13 mt-10">
		<legend>Market</legend>
		<select name="market" class="field" autocomplete="off">
		@foreach([
			"0"		=> "EBAY-US USA",
			'2'		=> "EBAY-ENCA Canada",
			'3'		=> "EBAY-GB Britain",
			'77'	=> "EBAY-DE Germany",
			'16'	=> "EBAY-AT Austria",
			'71'	=> "EBAY-FR France",
			'101'	=> "EBAY-IT Italy",
			'146'	=> "EBAY-NL Netherlands",
			'186'	=> "EBAY-ES Spain",
			'193'	=> "EBAY-CH Switzerland",
			'201'	=> "EBAY-HK Hong Kong",
			'205'	=> "EBAY-IE Ireland",
			'212'	=> "EBAY-PL Poland"
		] as $market => $country)
			<option @if($market == $cng->eBay['market']) selected @endif value="{{ $market }}">{{ $country }}</option>
		@endforeach
		</select>
	</fieldset>

	<div class="flex justify-between align-items-stretch mt-10 mb-20">
		<input type="search" name="search" placeholder="..." class="w-100 border-tiny rounded-3 p-1" required>
		<button type="submit" class="border-none w-52 rounded-3 active-bg symbol font-size-20 white-txt cursor-pointer hover-brightness">🔎</button>
	</div>

	<div class="filters flex align-items-stretch">
		<fieldset class="border-tiny font-size-13 max-w-400 ml-5">
			<legend>Condition</legend>
			<div class="columns-2">
			@foreach([
				'1000' => "New",
				'1500' => "New other (see details)",
				'1750' => "New with defects",
				'2000' => "Manufacturer refurbished",
				'2500' => "Seller refurbished",
				'3000' => "Used",
				'4000' => "Very Good",
				'5000' => "Good",
				'6000' => "Acceptable",
				'7000' => "For parts or not working"
			] as $code => $condition)
				@if(in_array($code, $cng->filters['Condition']))
				<label class="inline-block w-100"><input type="checkbox" value="{{ $code }}" name="Condition" checked> <span>{{ $condition }}</span></label>
				@else
				<label class="inline-block w-100"><input type="checkbox" value="{{ $code }}" name="Condition"> <span>{{ $condition }}</span></label>
				@endif
			@endforeach
			</div>
		</fieldset>

		<fieldset class="border-tiny font-size-13 pr-2">
			<legend>Listing Type</legend>
			@foreach([
				'StoreInventory'	=> "Store Inventory",
				'FixedPrice'		=> "Fixed Price",
				'Auction'			=> "Auction",
				'AuctionWithBIN'	=> "Auction With BIN",
				'Classified'		=> "Classified"
			] as $type=>$title)
			@if(in_array($type, $cng->filters['Listing Type']))
			<label class="block nowrap"><input type="checkbox" value="{{ $type }}" name="ListingType" checked> <span>{{ $title }}</span></label>
			@else
			<label class="block nowrap"><input type="checkbox" value="{{ $type }}" name="ListingType"> <span>{{ $title }}</span></label>
			@endif
			@endforeach
		</fieldset>

		<fieldset class="border-tiny font-size-13 w-100">
			<legend>Other</legend>
			@foreach([
				'HideDuplicateItems'	=> "Hide Duplicate Items",
				'FreeShippingOnly'		=> "Free Shipping Only",
				'ExcludeAutoPay'		=> "Exclude AutoPay",
				'BestOfferOnly'			=> "Best Offer Only"
			] as $name=>$title)
			@if(in_array($name, $cng->filters['Other']))
			<label class="block nowrap"><input type="checkbox" name="{{ $name }}" value="true" checked> <span>{{ $title }}</span></label>
			@else
			<label class="block nowrap"><input type="checkbox" name="{{ $name }}" value="true"> <span>{{ $title }}</span></label>
			@endif
			@endforeach
		</fieldset>
	</div>
	<script>
	(function(form){
		form.onsubmit = function(event){
			event.preventDefault();
			form.reload(1);
		}
		form.reload = function(offset){
			XHR.json({
				addressee: "/eBay/search",
				body: {
					sellers: (function(sellers){
						form.seller.forEach(function(seller){
							if(seller.checked){
								sellers.push(seller.value);
							}
						});
						return sellers;
					})([]),
					market: form.market.value,
					filters: (function(filters){
						if(form.pricelimits.checked){
							filters['MinPrice'] = form.min_price.value;
							filters['MaxPrice'] = form.max_price.value;
						}
						form.querySelectorAll(".filters input:checked").forEach(function(inp){
							if(filters[inp.name]){
								filters[inp.name].push(inp.value);
							}else filters[inp.name] = [inp.value];
						});
						return filters;
					})({}),
					category: {!! $category->id !!},
					offset: offset,
					queryRow: utf8_to_b64(form.search.value),
				},
				onsuccess: function(response){
					document.querySelector("#search-result").innerHTML = response;
				}
			});
		}
		form.market.onchange = function(){
			form.seller.forEach(function(inp){
				if(inp.dataset.market != form.market.value){
					inp.checked = false;
				}
			});
		}
		form.pricelimits.onchange=function(){
			form.min_price.disabled = !form.pricelimits.checked;
			form.max_price.disabled = !form.pricelimits.checked;
		}
		form.pricelimits.onchange();
	})(document.currentScript.parentNode)
	</script>
</form>
<div class="card mt-5 nowrap font-none overflow-hidden">
	<form class="white-bg wrap inline-block w-100 valign-top">
		<table width="100%" cellspacing="0" cellpadding="6">
			<thead>
				<tr class="dark-bg white-txt font-size-13">
					<th colspan="3" align="left" class="py-1">
						<button type="reset" title="Select All" class="tool icon transparent-bg border-none cursor-pointer valign-middle">
							<span class="icon-checkbox-checked light-txt font-size-20"></span>
						</button>
						<button type="submit" title="Import Selected" class="tool icon transparent-bg border-none cursor-pointer valign-middle">
							<span class="icon-fire light-txt font-size-20"></span>
						</button>
						<hr class="v-line gray-bg valign-middle inline-block">
					</th>
					<th width="60">Price</th>
					<th width="50">Selling</th>
					<th width="60">Condition</th>
					<th width="200">Store<span class="dark-txt"> / Seller</span></th>
				</tr>
			</thead>
			<tbody id="search-result" class="font-size-13" align="center">

			</tbody>
		</table>
		<script>
		(function(form){
			form.onreset = function(event){
				event.preventDefault();
				form.querySelectorAll("td>input[name='item']").forEach(function(inp){
					inp.checked = true;
				});
			}
			form.onsubmit = function(event){
				event.preventDefault();
				XHR.json({
					addressee: "/eBay/import",
					body: (function(items){
						form.querySelectorAll("td>input[name='item']:checked").forEach(function(inp){
							items.push({
								'eBay_id': inp.value,
								'StoreName': inp.dataset.store,
								'SellerName': inp.dataset.seller,
								'category': inp.dataset.category,
								'market': inp.dataset.market
							});
						});
						return items;
					})([]),
					onsuccess: function(response){
						response = JSON.parse(response);
						form.querySelectorAll("td>input[name='item']:checked").forEach(function(inp){
							if(isNaN(response.inArray(inp.value))){
								inp.parentNode.replaceChild(doc.create('span', {class: "red-txt"}, "Fail"), inp);
							}else{
								inp.parentNode.replaceChild(doc.create('span', {class: "green-txt"}, "Ok"), inp);
							}
						});
					}
				});
			}
		})(document.currentScript.parentNode)
		</script>
	</form>
	<div id="preparation-result" class="wrap inline-block valign-top w-100 h-25">
		<h1 class="font-size-20 p-1 m-0">Step 2</h1>
	</div>
	<script>
	var container = document.currentScript.parentNode;
	function containerShift(slide, speed){
		slide = slide || 0;
		speed = speed || 8;
		var	animate,
			offset = container.offsetWidth * slide;
		cancelAnimationFrame(animate);
		animate = requestAnimationFrame(function scrollSlide(){
			if(Math.abs(offset - container.scrollLeft) > 16){
				container.scrollLeft += (offset - container.scrollLeft) / speed;
				animate = requestAnimationFrame(scrollSlide);
			}else container.scrollLeft = offset;
		});
	}			

	function stepOne(event){
		event.preventDefault();
		containerShift(0);
	}
	containerShift(0);
	function stepTwo(event){
		event.preventDefault();
		containerShift(1);
		XHR.json({
			addressee: "/eBay/preparation",
			body: (function(items){
				document.querySelectorAll("#search-result>tr>td>input[name='item']:checked").forEach(function(inp){
					items.push(inp.value);
				});
				return {
					items: items,
					market: document.querySelector("#search").market.value
				};
			})([]),
			onsuccess: function(response){
				document.querySelector("#preparation-result").innerHTML = response;
			}
		});
	}
	function dropPrepareItem(event){
		event.preventDefault();
		var item = event.target.parentNode;
		item.parentNode.removeChild(item.previousElementSibling);
		item.parentNode.removeChild(item.nextElementSibling);
		item.parentNode.removeChild(item);
	}
	function dragOver(event){
  		event.preventDefault();
  		event.target.style.width = "20px";
	}
	function dragOut(event){
  		event.preventDefault();
  		event.target.style.width = "5px";
	}
	function dragKeyword(event){
		var frm = document.create('div', {
			class: "frm inline-block h-24 my-3 valign-middle",
			ondrop: "dropKeyword(event)",
			ondragover: "dragOver(event)",
			ondragleave: "dragOut(event)"
		});
		event.dataTransfer.effectAllowed = "move";
		event.target.parentNode.querySelectorAll('.keyword').forEach(function(keyword, i){
			
				if(keyword.contains(event.target)){
					event.dataTransfer.setData('Text', i);
				}else{
					setTimeout(function(){
					keyword.insertAdjacentElement("beforeBegin", frm.cloneNode(true));
					}, 200);
				}
		});
	}
	function dropKeyword(event){
  		event.preventDefault();
  		var form = event.target.parent('form');
		var offset = event.dataTransfer.getData('Text');
		var draggable = form.querySelectorAll('.keywords > .keyword')[offset];
		event.target.parentNode.replaceChild(draggable, event.target);
		draggable.parentNode.querySelectorAll('.frm').forEach(function(frm){
			frm.parentNode.removeChild(frm);
		});
		joinKeywords(form);
	}
	function removeKeyword(keyword){
		var form = keyword.parent('form');
		keyword.parentNode.removeChild(keyword);
		joinKeywords(form);
	}
	function joinKeywords(form){
		var keywords =[];
		form.querySelectorAll(".keywords > .keyword").forEach(function(keyword){
			keywords.push(keyword.textContent.trim());
		});
		form.named.value = keywords.join(" ");
		charCount(form.named);
	}
	function charCount(textarea){
		textarea.form.title_length.value = textarea.value.length;
		textarea.form.title_length.classList.toggle('red-txt', textarea.value.length > 80);
	}
	function dropImage(event){
		event.preventDefault();
		var slide = event.target.parentNode;
		var spinner = slide.parentNode;
			spinner.querySelectorAll(".slide").forEach(function(itm, i){
				if(slide.contains(itm)){
					var list = spinner.querySelector(".carousel-items");
						list.removeChild(list.querySelectorAll("label")[i]);
				}
		});
		spinner.removeChild(slide.previousElementSibling);
		spinner.removeChild(slide);
		spinner.querySelector("input").checked = "true";
	}
	function uploadData(event){
		event.preventDefault();
		event.target.parent(2).querySelectorAll(".tab > .tab-body > form").forEach(function(form, i){
			setTimeout(function(){
				XHR.json({
					addressee: "/eBay/addItem",
					body: (function(){
						return {
							Title: form.named.value.trim(),
							SKU: form.sku.value,
							CategoryID: form.CategoryID.value,
							ConditionID: form.ConditionID.value,
							ConditionDescription: form.ConditionDescription.value.trim(),
							Price: parseInt(form.price.value),
							Quantity: form.Quantity.value,
							DispatchTimeMax: form.DispatchTimeMax.value,
							itemSpecifics: (function(itemSpecifics){
								var specifics = form.querySelector("div > table.specifics > tbody");
								specifics.querySelectorAll("tr > td > input, tr > td > select").forEach(function(inp){
									if(inp.value.trim() && inp.value != "Unbekannt"){
										itemSpecifics.push({
											Name:	inp.name,
											Value:	inp.value.trim()
										});
									}
								});
								return itemSpecifics;
							})([]),
							ShippingDetails: {
								ShippingServiceOptions: (function(){
									var options = {
										ShippingService: form.ShippingService.value,
										ShippingServicePriority: 1
									};
									if(form.FreeShipping.checked){
										options['FreeShipping'] = "true";
									}else{
										options['FreeShipping'] = "false";
										options['ShippingServiceCost'] = form.ShippingServiceCost.value;
										options['ShippingServiceAdditionalCost'] = form.ShippingServiceAdditionalCost.value;
									}
									return options;
								})(),
								InternationalShippingServiceOption:{
									ShippingService: form.InternationalShippingService.value,
									ShipToLocation: form.InternationalShipToLocation.value,
									ShippingServicePriority: 1,
									ShippingServiceCost: form.InternationalShippingServiceCost.value,
									ShippingServiceAdditionalCost: form.InternationalShippingServiceAdditionalCost.value
								}
							},
							images: (function(imageset){
								form.querySelectorAll("div > .spinner > input").forEach(function(img){
									imageset.push(img.value);
								});
								return imageset;
							})([])
						};
					})(),
					onsuccess: function(response){
						response = JSON.parse(response);
						var btn = document.querySelector('#ftab-'+form.sku.value+' + label > .btn-sm');
						if(response['Ack'] == "Success"){
							var result = document.create('span',{class:"btn-sm green-txt inline-block w-86 text-center"}, response['Ack']);
							form.parentNode.removeChild(form);
						}else{
							var result = document.create('span',{class:"btn-sm red-txt inline-block w-86 text-center"}, response['Ack']);
							form.message.value = "ERROR: "+response['Errors']['LongMessage'];
						}
						btn.parentNode.replaceChild(result, btn);
					}
				});
			}, i * 100);
		});
	}
	</script>
</div>

<form autocomplete="on" class="card p-1 max-w-300 font-size-13 dark-txt my-5 white-bg">
	<div class="rounded-3 border-tiny p-1 flex align-items-stretch justify-between light-bg">
		<img src="/images/logotypes/excel.png" width="52">
		<div>
			<label class="block my-5">
				<input type="radio" name="period" value="hours" checked>
				<span>
					Last
					<input type="number" name="hours" value="1" min="1" class="field-size-30 font-size-15 text-bold text-center transparent-bg border-none"> h
				</span>
			</label>
			<label class="block my-5">
				<input type="radio" name="period" value="days">
				<span>
					Last
					<input type="number" name="days" value="1" min="1" class="field-size-30 font-size-15 text-bold text-center transparent-bg border-none"> d
				</span>
			</label>
		</div>
		<button class="border-none w-64 rounded-5 white-bg border-tiny cursor-pointer hover-active-bg">
			<div class="font-size-30">»</div>
			<div class="mb-5">Export</div>
		</button>
	</div>
	<script>
	(function(form){
		form.onsubmit = function(event){
			event.preventDefault();

			var sellers = [];
			document.querySelector("#search").seller.forEach(function(inp){
				if(inp.checked){
					sellers.push(inp.value)
				}
			});
			var period = (form.period.value == "days") ? (form.days.value * 24) : form.hours.value;
			window.open('/api/export/{!! $category->slug !!}/'+period+"/"+sellers.join("-"));
		}
	})(document.currentScript.parentNode)
	</script>
</form>