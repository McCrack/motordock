
<div class="accordion font-size-13">
	@foreach($items as $item)
	<input id="ftab-{{ $item->ItemID }}" type="radio" name="catalog" autocomplete="off" value="{{ $item->ItemID }}" class="catalog-item" hidden>
	<label for="ftab-{{ $item->ItemID }}" class="item tab-btn light-bg flex justify-between align-items-center">
		<img		
		width="64"
		height="58"
		src="{{ $item->GalleryURL }}"
		class="fit-cover mr-10">
		<div class="font-size-14 dark-txt w-450">
			<span class=" w-128 text-center text-regular font-size-12 silver-txt">id: {{ $item->ItemID }}</span>
			<div>{{ $item->Title }}</div>
		</div>
		
		<div class="price mr-20 w-128 black-txt text-tiny text-right"><span class="text-bold">{{ $item->CurrentPrice }}</span></div>

		<div class="font-size-12 text-center">{{ $Carbon->parse($item->EndTime)->format('d.m.Y H:i:s') }}</div>

		<div class="w-128 mx-10 text-center">
			<div class="btn btn-sm btn-primary">{{ $item->ListingType }}</div>
		</div>
		<button onclick="dropPrepareItem(event)" class="btn btn-sm btn-danger mr-20">╳</button>
	</label>
	<div class="tab white-bg">
		<div class="tab-body level mb-10 font-size-14">
			<form class="my-10 flex align-items-start flex-wrap" autocomplete="off">
				<div class="w-50 pl-1 border-box">
    				<output name="message" class="red-txt my-20 font-size-14"></output>
    				<p class="my-10"><b>SKU</b>: <output name="sku" class="text-regular">{{ $item->ItemID }}</output></p>
    				<select name="CategoryID" class="w-100 field">
						<option value="{{ $item->PrimaryCategoryID }}" selected>{{ $item->PrimaryCategory }}</option>
						@isset($item->SecondaryCategory)
						<option value="{{ $item->SecondaryCategoryID }}">{{ $item->SecondaryCategory }}</option>
						@endif
					</select>
	    			<div class="border-tiny font-size-14 my-10">
	    				<div class="keywords p-1" oninput="joinKeywords(this.parent('form'))">
	    					<div class="keyword btn-danger" draggable='true' ondragstart='dragKeyword(event)'>
	    						<hr>
	    						<div contenteditable="true">{{ $item->PrimaryCategory }}</div>
	    						<span class="drop" onclick="removeKeyword(this.parentNode)"></span>
	    					</div>
	    					{!! implode(" ", $item->keywords) !!}
	    				</div>
	    			</div>
	    			<div class="font-size-13 text-regular text-right">Char count: <output class="text-bold @if(strlen($item->named) > 80) red-txt @endif" name="title_length">{{ strlen($item->named) }}</output></div>
	    			<textarea oninput="charCount(this)" name="named" class="resize-vertical w-100 h-52 mb-5">{{ $item->named }}</textarea>
					<span class="text-regular">Condition</span>:<br>
					<select name="ConditionID" class="field">
					@foreach([
						1000 => "New",
						1500 => "New other (see details)",
						1750 => "New with defects",
						2000 => "Manufacturer refurbished",
						2500 => "Seller refurbished",
						2750 => "Like New",
						3000 => "Used",
						4000 => "Very Good",
						5000 => "Good",
						6000 => "Acceptable",
						7000 => "For parts or not working"
					] as $id => $name)
						<option value="{{ $id }}" @if($id == $item->ConditionID) selected @endif>{{ $name }}</option>
					@endforeach
					</select>
					<textarea name="ConditionDescription" class="resize-vertical btn-light w-100 h-52 mt-5 mb-10">{{ $item->ConditionDescription }}</textarea>
					<div class="flex justify-between align-items-start">
						<div>
							<span class="text-regular">Pice</span>:
							<span class="text-bold">{{ $item->CurrentPrice }}</span> =>
							<input name="price" class="field" value="{{ $item->price }}" size="6">
							<input name="currency" class="field field-size-40" value="{{ $cng->eBay['Currency'] }}" readonly>
						</div>
						<div class="text-right">
							<p class="my-5">Quantity - <input name="Quantity" type="number" class="field field-size-40" value="1" min="1"></p>
	    					<p class="my-5">Dispatch Time Max - <input name="DispatchTimeMax" type="number" class="field field-size-60" value="{{ $cng->eBay['DispatchTimeMax'] }}" min="0"></p>
	    				</div>
					</div>
	    			<p class="text-bold font-size-18 mt-0">Specifics:</p>
	    			<table width="100%" cellspacing="2" cellpadding="0" class="specifics font-size-13 border-tiny">
						<col width="55%"><col width="45%">
						<tbody>
	    					@foreach($item->specifics as $specific => $options)
							<tr align="center">
								<td>{{ $specific }}</td>
								<td>
									@if($options['SelectionMode'] == "SelectionOnly")
									<select name="{{ $specific }}" class="field w-100">
										@empty($options['Value'])
										<option value="Unbekannt">Unbekannt</option>
										@endempty
										@foreach($options['recommendations'] as $value)
										@if($value == ($options['Value'] ?? "Unbekannt"))
										<option value="{{ $value }}" selected>{{ $value }}</option>
										@else
										<option value="{{ $value }}">{{ $value }}</option>
										@endif
										@endforeach
									</select>
									@else
										
									<input name="{{ $specific }}" class="field w-100" value="{!!
										(is_array($options['Value'] ?? null)
											? implode('; ', $options['Value'])
											: ($options['Value'] ?? null))
										!!}">
									@endif
								</td>
							</tr>
	    					@endforeach
	    				</tbody>
	    			</table>
					<fieldset class="border-tiny font-size-13 mt-10">
						<legend>Shipping Service</legend>
						<select name="ShippingService" class="field btn-success mt-5">
						@foreach($shippingServices as $service)
							@if(($service['ValidForSellingFlow'] == "true") && empty($service['InternationalService']))
							<option @if($service['ShippingService'] == $cng->eBay['ShippingDetails']['ShippingServiceOptions']['ShippingService']) selected @endif value="{{ $service['ShippingService'] }}">{{ $service['ShippingService'] }}</option>
							@endif
						@endforeach
						</select>
						<label class="block nowrap mt-10">
							<input name="FreeShipping" type="checkbox" @if($cng->eBay['ShippingDetails']['ShippingServiceOptions']['FreeShipping'] == "true") checked @endif>
							<span>Free Shipping</span>
						</label>
						<div class="mt-10">
			  				<input name="ShippingServiceCost" value="{{ $item->ShippingCost }}" class="field" size="10">
			  				Shipping Service Cost
						</div>
						<div class="mt-10">
			  				<input name="ShippingServiceAdditionalCost" value="{{ $cng->eBay['ShippingDetails']['ShippingServiceOptions']['ShippingServiceAdditionalCost'] }}" class="field" size="10">
			  				Shipping Service Additional Cost
						</div>
					</fieldset>
					<fieldset class="border-tiny font-size-13 mt-5">
						<legend>International Shipping Service</legend>
						<select name="InternationalShippingService" class="field btn-primary mt-5">
							@foreach($shippingServices as $service)
							@if(($service['ValidForSellingFlow'] == "true") && isset($service['InternationalService']))
							<option @if($service['ShippingService'] == $cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShippingService']) selected @endif value="{{ $service['ShippingService'] }}">{{ $service['ShippingService'] }}</option>
							@endif
							@endforeach
						</select>
						<div>
							<select name="InternationalShipToLocation" class="field field-size-100 mt-5">
							@foreach([
								"Worldwide"
							] as $option)
								<option @if($option == ($cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShipToLocation'] ?? null)) selected @endif value="{{ $option }}">{{ $option }}</option>
							@endforeach
							</select>
							Ship To Location
						</div>
						<div class="mt-10">
							<input name="InternationalShippingServiceCost" value="{{ $cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShippingServiceCost'] }}" class="field field-size-100">
							Shipping Service Cost
						</div>
						<div class="mt-10">
							<input name="InternationalShippingServiceAdditionalCost" value="{{ $cng->eBay['ShippingDetails']['InternationalShippingServiceOption']['ShippingServiceAdditionalCost'] }}" class="field field-size-100">
							Shipping Service Additional Cost
						</div>
					</fieldset>
    			</div>
				<div class="w-50 px-1 border-box font-size-13 text-medium">

					@if(is_array($item->PictureURL))
    				<div class="spinner mt-10">
    					@foreach($item->PictureURL as $i=>$img)
        				<input id="s-{{ $item->ItemID }}-{{ $i }}" type="radio" name="spinner-{{ $item->ItemID }}" value="{{ $img }}" hidden @if($loop->first) checked @endif>
        				<div class="slide">
            				<img src="{{ $img }}" class="fit-contain">
            				<button onclick="dropImage(event)" class="absolute top right z-index-2 w-24 h-24 text-center font-size-16 white-bg border-tiny p-0">╳</button>
    	    			</div>
	    				@endforeach
						<div class="carousel-items justify-center py-1">
        					@foreach($item->PictureURL as $i=>$img)
        					<label for="s-{{ $item->ItemID }}-{{ $i }}" class="m-5 max-w-100">
            					<img src="{{ $img }}" class="fit-cover h-100 w-100">
        					</label>
        					@endforeach
						</div>
	    			</div>
	    			@else
	    			<div class="spinner mt-10">
	    				<input type="radio" value="{{ $item->PictureURL }}" hidden checked>
						<div class="slide">
            				<img src="{{ $item->PictureURL }}" class="fit-contain">
    	    			</div>
					</div>
	    			@endif

					<ul class="list-none">
						<p class="text-bold font-size-16 mb-5">Seller:</p>
						<li class="my-4">UserID - <b>{{ $item->Seller['UserID'] }}</b></li>
						<li class="my-4">Feedback Rating Star - <b>{{ $item->Seller['FeedbackRatingStar'] }}</b></li>
						<li class="my-4">Feedback Score - <b>{{ $item->Seller['FeedbackScore'] }}</b></li>
						<li class="my-4">Positive Feedback Percente - <b class="red-txt">{{ $item->Seller['PositiveFeedbackPercent'] }}</b></li>
						@isset($item->Storefront)
						<li class="my-4">
							Store Name - <a class="blue-txt hover-underline hover-active-txt" href="$item->Storefront['StoreURL']" target="_blank">{{ $item->Storefront['StoreName'] }}</a>
						</li>
						@endisset
					</ul>
					<ul class="list-none">
						<p class="text-bold font-size-16 mb-5">ReturnPolicy:</p>
						@isset($item->ReturnPolicy['ReturnsWithin'])
						<li class="my-4">Returns Within - <b>{{ $item->ReturnPolicy['ReturnsWithin'] }}</b></li>
						@endisset
						@isset($item->ReturnPolicy['ShippingCostPaidBy'])
						<li class="my-4">Shipping Cost Paid By</td> - <b>{{ $item->ReturnPolicy['ShippingCostPaidBy'] }}</b></li>
						@endisset
					</ul>
					<hr class="h-line gray-bg">
					<ul class="list-none">
						<li class="my-4">Listing Status - <b>{{ $item->ListingStatus }}</b></li>
						<li class="my-4">AutoPay - <b>{{ (($item->AutoPay == "true")) ? "Yes" : "No" }}</b></li>
						<li class="my-4 flex align-items-center justify-start">
							<span class="nowrap">Payment Methods - </span>
							<div class="mx-4">
							@if(is_array($item->PaymentMethods))
								<span class="btn btn-sm btn-success inline-block my-1">{!! implode("</span> <span class='btn btn-sm btn-dark inline-block my-1'>", $item->PaymentMethods) !!}</span>
							@else
								<span class="btn btn-sm btn-success ml-4">{{ $item->PaymentMethods }}</span>
							@endif
							</div>
						</li>
						<li class="my-4">Listing Type - <b>{{ $item->ListingType }}</b></li>
						<li class="my-4">Condition - <b class="red-txt">{{ $item->ConditionDisplayName }}</b></li>
						<li class="my-4">Quantity - <b>{{ $item->Quantity }}</b></li>
						<li class="my-4">Location - <b>{{ $item->Location }}</b></li>
						<li class="my-4">Global Shipping - <b>{{ (($item->GlobalShipping == "true") ? "Yes" : "No") }}</b></li>
						<li class="my-4">Handling Time - <b>{{ $item->HandlingTime }}</b></li>
						<li class="my-4">Hit Count - <b>{{ $item->HitCount }}</b></li>
						<li class="my-4">Start Time - {{ $Carbon->parse($item->StartTime)->format('d.m.Y H:i:s') }}</li>
						<li class="my-4">End Time - <span class="red-txt">{{ $Carbon->parse($item->EndTime)->format('d.m.Y H:i:s') }}</span></li>
					</ul>
				</div>
			</form>
		</div>
	</div>
	@endforeach
	<br class="clear-left">
</div>
<div class="text-right py-1">
	<button class="btn btn-dark btn-lg mr-10" onclick="stepOne(event)">« Back</button>
	@if($items->count > 0)
	<button class="btn btn-danger btn-lg mr-10" onclick="uploadData(event)">Upload »</button>
	@endif
</div>