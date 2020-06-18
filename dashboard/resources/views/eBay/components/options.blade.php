@php
//dd($cng->eBay['price']['price gradation']);
@endphp
<form autocomplete="off" class="card white-bg p-2 mt-10">
	
	<fieldset class="float-right font-size-13 w-256 border-tiny rounded-3 white-bg">
		<legend>Access</legend>
		<div class="columns-3">
			@foreach([
				"admin","developer","manager","partner","guest"
			] as $group)
			<label class="inline-block w-100 nowrap">
				<input type="checkbox" name="access" @if(in_array($group, $cng->access)) checked @endif value="{{ $group }}">
				<span>{{ $group }}</span>
			</label>
		@endforeach
		</div>
	</fieldset>

	<p class="font-size-30 text-bold my-20"><span class="icon-cog"></span> Options</p>

	<fieldset class="border-tiny font-size-13 p-2 light-bg">
		<legend class="font-size-16">Price Formation</legend>

		<fieldset class="border-none float-left pl-0">
			<legend>Tax:</legend>
			<input name="tax" class="field" type="text" value="{{ $cng->eBay['price']['Tax'] }}" size="15">
		</fieldset>
		<fieldset class="border-none float-left">
			<legend>eBay tax:</legend>
			<input name="eBayTax" class="field" type="text" value="{{ $cng->eBay['price']['eBay Tax'] }}" size="15">
		</fieldset>
		<fieldset class="border-none">
			<legend>Currency Rate:</legend>
			<input name="currencyRate" class="field" type="text" value="{{ $cng->eBay['price']['Currency Rate'] }}" size="15">
		</fieldset>

		<fieldset class="float-right w-50 border-none mt-10 pr-0">
			<legend class="font-size-16">Special Price Formules</legend>
			<table class="border-tiny white-bg" width="100%" cellpadding="5" cellspacing="0">
				<thead>
					<tr class="dark-bg light-txt" align="center">
						<th width="28"></th>
						<th width="120px">Category ID</th>
						<th>Formula</th>
						<th width="28"></th>
					</tr>
				</thead>
				<tbody class="price-formules">
				@if(count($cng->price['special formules']) > 0)
					@foreach($cng->price['special formules'] as $key=>$val)
					<tr align="center">
						<th class="white-bg cursor-pointer" title="Add row" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true">{{ $key }}</td>
						<td contenteditable="true">{{ $val }}</td>
						<th class="white-bg cursor-pointer" title="Delete row" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					@endforeach
				@else
					<tr align="center">
						<th class="white-bg cursor-pointer" title="Add row" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<td contenteditable="true"></td>
						<th class="white-bg cursor-pointer" title="Delete row" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
				@endif
				</tbody>
			</table>
		</fieldset>

		<fieldset class="border-none mt-10 pl-0">
			<legend class="font-size-16">Price Gradation</legend>
			<table class="border-tiny white-bg" width="100%" cellpadding="5" cellspacing="0">
				<thead>
					<tr class="dark-bg light-txt" align="center">
						<th width="28"></th>
						<th width="45%">Threshold value</th>
						<th>Ratio</th>
						<th width="28"></th>
					</tr>
				</thead>
				<tbody class="price-gradation">
				@if(count($cng->eBay['price']['price gradation']) > 0)
					@foreach($cng->eBay['price']['price gradation'] as $key=>$val)
					<tr align="center">
						<th class="white-bg cursor-pointer" title="Add row" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true">{{ $key }}</td>
						<td contenteditable="true">{{ $val }}</td>
						<th class="white-bg cursor-pointer" title="Delete row" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					@endforeach
				@else
					<tr align="center">
						<th class="white-bg cursor-pointer" title="Add row" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<td contenteditable="true"></td>
						<th class="white-bg cursor-pointer" title="Delete row" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
				@endif
				</tbody>
			</table>
		</fieldset>
	</fieldset>

	<script>
	(function(form){
		var timeout;
		form.oninput = function(event){
			clearTimeout(timeout);
			timeout = setTimeout(function(){
				XHR.json({
					addressee: "/eBay/sv_options",
					body: (function(){
						if(event.target.name == "access"){
							return {
								access: (function(access){
									form.querySelectorAll("input[name='access']:checked").forEach(function(inp){
										access.push(inp.value)
									});
									return access;
								})([])
							}
						}else{
							return {
								price: {
									'Tax': form.tax.value,
									'eBay Tax': form.eBayTax.value,
									'Currency Rate': form.currencyRate.value,
									'price gradation': (function(grad, key){
										form.querySelectorAll(".price-gradation > tr > td").forEach(function(cell, i){
											if(i%2 && key){
												grad[key] = cell.textContent.trim();
											}else if(cell.textContent.length) {
												key = cell.textContent.trim();
											}
										});
										return grad;
									})({}),
									'special formules': (function(formules){
										form.querySelectorAll(".price-formules > tr > td").forEach(function(cell, i){
											if(i%2 && key){
												formules[key] = cell.textContent.trim();
											}else if(cell.textContent.length) {
												key = cell.textContent.trim();
											}
										});
										return formules;
									})({})
								}
							}
						}
					})(),
					onsuccess: function(response){
						SaveIndicator.checked = false;
					}
				});
			},2000);
			SaveIndicator.checked = true;
		}
	})(document.currentScript.parentNode)
	</script>
</form>

<form autocomplete="off">
	<div class="card white-bg mt-5 p-2">
		<fieldset class="clear-right float-right border-tiny font-size-13">
			<legend>Limits of Price</legend>
			<input type="number" name="Min Price" value="{{ $cng->{'Min Price'} }}" min="1" class="field field-size-60" title="Min Price">
			<span class="mx-10">to</span>
			<input type="number" name="Max Price" value="{{ $cng->{'Max Price'} }}" min="2" class="field field-size-60" title="Max Price">
		</fieldset>

		<fieldset class="float-right font-size-13 border-tiny">
			<legend>Default Category</legend>
			<select name="default category" class="field">
				@foreach($favorites as $cat)
				<option @if($cat->id == $cng->{'default category'}) selected @endif value="{{ $cat->id }}">{{ $cat->name['de'] }}</option>
				@endforeach
			</select>
		</fieldset>

		<fieldset class="float-right font-size-13 border-tiny">
			<legend>Default Market</legend>
			<select name="market" class="field field-size-100">
				@foreach($cng->markets as $id => $market)
				<option @if($id == $cng->eBay['market']) selected @endif value="{{ $id }}">{{ $market }}</option>
				@endforeach
			</select>
		</fieldset>

		<fieldset class="float-right font-size-13 border-tiny">
			<legend>My Market</legend>
			<select name="market" class="field field-size-100">
			@foreach($cng->markets as $id => $market)
				@foreach($cng->markets as $id => $market)
				<option @if($id == $cng->eBay['myMarket']) selected @endif value="{{ $id }}">{{ $market }}</option>
				@endforeach
			@endforeach
			</select>
		</fieldset>

		<p class="font-size-20 text-bold my-5">Research Filters</p>

		<div class="clear-right filters flex align-items-stretch">
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
					<label class="inline-block w-100">
						<input type="checkbox" value="{{ $code }}" name="Condition" checked> <span>{{ $condition }}</span>
					</label>
					@else
					<label class="inline-block w-100">
						<input type="checkbox" value="{{ $code }}" name="Condition"> <span>{{ $condition }}</span>
					</label>
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
				<label class="block nowrap">
					<input type="checkbox" value="{{ $type }}" name="Listing Type" checked> <span>{{ $title }}</span>
				</label>
				@else
				<label class="block nowrap">
					<input type="checkbox" value="{{ $type }}" name="Listing Type"> <span>{{ $title }}</span>
				</label>
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
				<label class="block nowrap"><input type="checkbox" name="Other" value="{{ $name }}" checked> <span>{{ $title }}</span></label>
				@else
				<label class="block nowrap"><input type="checkbox" name="Other" value="{{ $name }}"> <span>{{ $title }}</span></label>
				@endif
				@endforeach
			</fieldset>
		</div>

	</div>

	<div class="card white-bg mt-5 p-2 flex justify-between align-items-streach">
		<div class="w-50 mx-10">
			<div>AppID</div>
			<input name="AppID" value="{{ $cng->AppID }}" class="field w-100 mb-10">
			<div>DevID</div>
			<input name="DevID" value="{{ $cng->DevID }}" class="field w-100 mb-10">
		</div>
		<div class="w-50 mx-10">
			<div>CertID</div>
			<input name="CertID" value="{{ $cng->CertID }}" class="field w-100 mb-10">
			<div>RuName</div>
			<input name="RuName" value="{{ $cng->RuName }}" class="field w-100 mb-10">
		</div>
	</div>

	<div class="card white-bg mt-5 p-2 columns-4 font-size-13">
		<div class="my-5 inline-block w-100">
			Site
			<input name="Site" value="{{ $cng->eBay['Site'] }}" class="field w-100">
		</div>
		<div class="my-5 inline-block w-100">
			<input name="Country" value="{{ $cng->eBay['Country'] }}" class="field" size="10">
			Country
		</div>
		<div class="my-5 inline-block w-100">
			<input name="Location" value="{{ $cng->eBay['Location'] }}" class="field" size="10">
			Location
		</div>
		<div class="my-5 inline-block w-100">
			Payment Methods
			<input name="PaymentMethods" value="{{ $cng->eBay['PaymentMethods'] }}" class="field w-100">
		</div>
		<div class="my-5 inline-block w-100">
			<input name="Currency" value="{{ $cng->eBay['Currency'] }}" class="field" size="10">
			Currency
		</div>
		<div class="my-5 inline-block w-100">
			<input name="PostalCode" value="{{ $cng->eBay['PostalCode'] }}" class="field" size="10">
			Postal Code
		</div>
		<div class="my-5 inline-block w-100">
			Pay Pal Email Address
			<input name="PayPalEmailAddress" value="{{ $cng->eBay['PayPalEmailAddress'] ?? null }}" class="field w-100 btn-danger">
		</div>
		<div class="my-5 inline-block w-100">
			<input name="DispatchTimeMax" value="{{ $cng->eBay['DispatchTimeMax'] }}" type="number" class="field btn-primary field-size-80">
			Dispatch Time Max
		</div>
		<div class="my-5 inline-block w-100">
			<input name="ListingDuration" value="{{ $cng->eBay['ListingDuration'] }}" class="field field-size-80">
			Listing Duration
		</div>
		<div class="my-5 inline-block w-100">
			Hit Counter
			<select name="HitCounter" class="field w-100">
				@foreach([
					"BasicStyle",
					"HiddenStyle",
					"NoHitCounter",
					"RetroStyle"
				] as $option)
				<option @if($option == $cng->eBay['HitCounter']) selected @endif value="{{ $option }}">{{ $option }}</option>
				@endforeach
			</select>
		</div>
		<label class="inline-block w-100 nowrap my-10">
			<input @if($cng->eBay['IncludeRecommendations'] == "true") checked @endif type="checkbox" name="IncludeRecommendations">
			<span>Include Recommendations</span>
		</label>

		<label class="inline-block w-100 nowrap my-10">
			<input @if($cng->eBay['PrivateListing'] == "true") checked @endif type="checkbox" name="PrivateListing">
			<span>Private Listing</span>
		</label>
	</div>

	<p class="mb-0 text-bold font-size-18 blue-txt">Return Policy</p>
	<div class="card white-bg mt-5 p-2">
      	<div class="columns-3 font-size-13">
			<div class="my-5 inline-block w-100">
				Returns Accepted Option
				<select name="ReturnPolicy.ReturnsAcceptedOption" class="field w-100">
					@foreach([
						'Returns Accepted' => "ReturnsAccepted",
						'Returns Not Accepted' => "ReturnsNotAccepted"
					] as $key => $val)
					<option @if($val == $cng->eBay['ReturnPolicy']['ReturnsAcceptedOption']) selected @endif value="{{ $val }}">{{ $key }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				Refund Option
				<select name="ReturnPolicy.RefundOption" class="field w-100">
					@foreach([
						'Money Back' => "MoneyBack",
						'Money Back Or Replacement' => "MoneyBackOrReplacement",
						'Money Back Or Exchange' => "MoneyBackOrExchange"
					] as $key => $val)
					<option @if($val == ($cng->eBay['ReturnPolicy']['RefundOption'] ?? null)) selected @endif value="{{ $val }}">{{ $key }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
			  	Shipping Cost Paid By Option
				<select name="ReturnPolicy.ShippingCostPaidByOption" class="field w-100">
					@foreach([
						"Buyer",
						"Seller"
					] as $option)
					<option @if($option == $cng->eBay['ReturnPolicy']['ShippingCostPaidByOption']) selected @endif value="{{ $option }}">{{ $option }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				Returns Within Option
				<select name="ReturnPolicy.ReturnsWithinOption" class="field w-100">
					@foreach([
						'Days 14' => "14_Days",
						'Days 30' => "30_Days",
						'Days 60' => "60_Days"
					] as $key => $val)
					<option @if($val == $cng->eBay['ReturnPolicy']['ReturnsWithinOption']) selected @endif value="{{ $val }}">{{ $key }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				International Returns Accepted Option
				<select name="ReturnPolicy.InternationalReturnsAcceptedOption" class="field w-100">
					@foreach([
						"Returns Accepted"	=> "ReturnsAccepted",
						"Returns Not Accepted"=> "ReturnsAccepted"
					] as $key => $val)
					<option @if($val == $cng->eBay['ReturnPolicy']['InternationalReturnsAcceptedOption']) selected @endif value="{{ $val }}">{{ $key }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				International Refund Option
				<select name="ReturnPolicy.InternationalRefundOption" class="field w-100">
					@foreach([
						'Money Back' => "MoneyBack",
						'Money Back Or Replacement' => "MoneyBackOrReplacement",
						'Money Back Or Exchange' => "MoneyBackOrExchange"
					] as $key => $val)
					<option @if($val == ($cng->eBay['ReturnPolicy']['InternationalRefundOption'] ?? null)) selected @endif value="{{ $val }}">{{ $key }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				International Shipping Cost Paid By Option
				<select name="ReturnPolicy.InternationalShippingCostPaidByOption" class="field w-100">
					@foreach([
						"Buyer",
						"Seller"
					] as $option)
					<option @if($option == $cng->eBay['ReturnPolicy']['InternationalShippingCostPaidByOption']) selected @endif value="{{ $option }}">{{ $option }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				International Returns Within Option
				<select name="ReturnPolicy.InternationalReturnsWithinOption" class="field w-100">
					@foreach([
						'Days 14' => "14_Days",
						'Days 30' => "30_Days",
						'Days 60' => "60_Days"
					] as $key => $val)
					<option @if($val == $cng->eBay['ReturnPolicy']['InternationalReturnsWithinOption']) selected @endif value="{{ $val }}">{{ $key }}</option>
					@endforeach
				</select>
			</div>
			<div class="my-5 inline-block w-100">
				Description
				<textarea name="ReturnPolicy.Description" placeholder="..." class="resize-vertical h-200 w-100">{{ $cng->eBay['ReturnPolicy']['Description'] ?? null }}</textarea>
			</div>
		</div>
	</div>
	<p class="mb-0 text-bold font-size-18 blue-txt">Shipping Details</p>
	<div class="card white-bg mt-5 p-2">
		<div class="float-left w-256 mr-30 ml-10">
			<label class="block nowrap mb-20">
				<input type="checkbox" @if($cng->eBay['ShippingDetails']['GlobalShipping'] == "true") checked @endif name="ShippingDetails.GlobalShipping">
				<span>Global Shipping</span>
			</label>
			<div class="ml-10">
				<select name="ShippingDetails.ShippingType" class="field field-size-100 mt-5">
					@foreach([
						"Flat",
						"Calculated",
						"NotSpecified"
					] as $option)
					<option @if($option == $cng->eBay['ShippingDetails']['ShippingType']) selected @endif value="{{ $option }}">{{ $option }}</option>
					@endforeach
				</select>
				Shipping Type
			</div>
			<div class="mt-10 ml-10">
				<select name="ShipToLocations" class="field field-size-100 mt-5">
					@foreach([
						"Worldwide"
					] as $option)
					<option @if($option == $cng->eBay['ShipToLocations']) selected @endif value="{{ $option }}">{{ $option }}</option>
					@endforeach
				</select>
				Ship To Locations
			</div>
		</div>
		<fieldset class="border-tiny font-size-13 mb-10 px-1 h-160">
			<legend>Calculated Shipping Rate</legend>
			<div class="mt-10">
				<input name="ShippingDetails.CalculatedShippingRate.PackagingHandlingCosts" value="{{ $cng->eBay['ShippingDetails']['CalculatedShippingRate']['PackagingHandlingCosts'] }}" class="field" size="7">
				Packaging Handling Costs
			</div>
			<div class="mt-10">
				<input name="ShippingDetails.CalculatedShippingRate.InternationalPackagingHandlingCosts" value="{{ $cng->eBay['ShippingDetails']['CalculatedShippingRate']['InternationalPackagingHandlingCosts'] }}" class="field" size="7">
				International Packaging Handling Costs
			</div>
      		<div class="mt-10">
			  <input name="ShippingDetails.CalculatedShippingRate.OriginatingPostalCode" value="{{ $cng->eBay['ShippingDetails']['CalculatedShippingRate']['OriginatingPostalCode'] }}" class="field" size="7">
			  Originating Postal Code
			</div>
		</fieldset>

		<fieldset class="border-tiny font-size-13 py-1 float-left h-180">
			<legend>Shipping Service</legend>
			<label class="block nowrap mt-10 mb-10">
				<input name="ShippingDetails.ShippingServiceOptions.FreeShipping" type="checkbox" @if($cng->eBay['ShippingDetails']['ShippingServiceOptions']['FreeShipping'] == "true") checked @endif>
				<span>Free Shipping</span>
			</label>
			<div class="mt-10">
				<select name="ShippingDetails.ShippingServiceOptions.ShippingService" class="field btn-success">
				@foreach($services as $service)
					@if(($service['ValidForSellingFlow'] == "true") && empty($service['InternationalService']))
					<option @if($service['ShippingService'] == $cng->eBay['ShippingDetails']['ShippingServiceOptions']['ShippingService']) selected @endif value="{{ $service['ShippingService'] }}">{{ $service['ShippingService'] }}</option>
					@endif
				@endforeach
				</select>
				Shipping Service
			</div>
			<div class="mt-10">
			  <input name="ShippingDetails.ShippingServiceOptions.ShippingServiceCost" value="{{ $cng->eBay['ShippingDetails']['ShippingServiceOptions']['ShippingServiceCost'] }}" class="field" size="10">
			  Shipping Service Cost
			</div>
			<div class="mt-10">
			  <input name="ShippingDetails.ShippingServiceOptions.ShippingServiceAdditionalCost" value="{{ $cng->eBay['ShippingDetails']['ShippingServiceOptions']['ShippingServiceAdditionalCost'] }}" class="field" size="10">
			  Shipping Service Additional Cost
			</div>
		</fieldset>

		<fieldset class="border-tiny font-size-13 py-1 h-180">
			<legend>International Shipping Service 1</legend>
			<div>
				<select name="ShippingDetails.InternationalShippingServiceOption.ShipToLocation" class="field field-size-100 mt-5">
					@foreach([
						"Worldwide"
					] as $option)
					<option @if($option == ($cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShipToLocation'] ?? null)) selected @endif value="{{ $option }}">{{ $option }}</option>
					@endforeach
				</select>
				Ship To Location
			</div>
			<div class="mt-10">
				<select name="ShippingDetails.InternationalShippingServiceOption.ShippingService" class="field btn-primary">
				@foreach($services as $service)
					@if(($service['ValidForSellingFlow'] == "true") && isset($service['InternationalService']))
					<option @if($service['ShippingService'] == $cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShippingService']) selected @endif value="{{ $service['ShippingService'] }}">{{ $service['ShippingService'] }}</option>
					@endif
				@endforeach
				</select>
			  Shipping Service
			</div>
			<div class="mt-10">
				<input name="ShippingDetails.InternationalShippingServiceOption.ShippingServiceCost" value="{{ $cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShippingServiceCost'] }}" class="field" size="10">
				Shipping Service Cost
			</div>
			<div class="mt-10">
				<input name="ShippingDetails.InternationalShippingServiceOption.ShippingServiceAdditionalCost" value="{{ $cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShippingServiceAdditionalCost'] }}" class="field" size="10">
				Shipping Service Additional Cost
			</div>
		</fieldset>
	</div>

	<script>
	(function(form){
		var timeout;
		form.oninput = function(event){
			clearTimeout(timeout);
			timeout = setTimeout(function(){
				if(SaveIndicator.checked){
					var field = {};
						field[event.target.name] = (event.target.type == "checkbox") ? event.target.checked : event.target.value;
					form.save(field);
				}
			},2000);
			SaveIndicator.checked = true;
		}
		form.onchange = function(event){
			if(SaveIndicator.checked){
				var field = {};
				switch(event.target.name){
					case "Condition":
					case "Listing Type":
					case "Other":
						field['filters'] = {
							'Condition': [],
							'Listing Type': [],
							'Other': []
						};
						form.querySelectorAll(".filters input:checked").forEach(function(inp){
							field['filters'][inp.name].push(inp.value)
						});
						break;
					default:
						field[event.target.name] = (event.target.type == "checkbox") ? event.target.checked : event.target.value;
						break;
				}
				form.save(field);
			}
		}
		form.save = function(field){
			XHR.json({
				addressee: "/eBay/sv_options",
				body: field,
				onsuccess: function(response){
					if(parseInt(response)){
						SaveIndicator.checked = false;
					}
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</form>