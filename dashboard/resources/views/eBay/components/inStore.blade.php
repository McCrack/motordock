<div class="card accordion white-bg mt-10 font-size-13">
	@foreach($items as $item)
	<input id="atab-{{ $item->ItemID }}" type="radio" name="catalog" autocomplete="off" value="{{ $item->ItemID }}" class="catalog-item" hidden>
	<label for="atab-{{ $item->ItemID }}" class="item tab-btn flex justify-between align-items-center">
		<img		
		width="64"
		height="58"
		src="{{ $item->PictureDetails['GalleryURL'] }}"
		class="fit-cover mr-10">
		<div class="font-size-14 dark-txt w-450">
			<span class=" w-128 text-center text-regular font-size-12 silver-txt">id: {{ $item->ItemID }}</span>
			<div>{{ $item->Title }}</div>
		</div>
		
		<div class="price mr-20 w-128 black-txt text-tiny text-right"><span class="text-bold">{{ $item->StartPrice }} {{ $item->Currency }}</span></div>

		<div>
			<div class="font-size-12">{{ $item->ListingDetails['StartTime'] }}</div>
			<div class="font-size-12">{{ $item->ListingDetails['EndTime'] }}</div>
		</div>

		<div class="w-128 mx-20 text-center">
			<div class="font-size-12 blue-txt">
				{{ $item->ListingType }}
				<span class="dark-txt">→</span>
			</div>
			<div class="btn btn-sm btn-primary">{{ $item->SellingStatus['ListingStatus'] }}</div>
		</div>
		
	</label>
	<div class="tab white-bg">
		<div class="tab-body level mb-10">
			
		</div>
	</div>
	@endforeach
	<br class="clear-left">
</div>

<form class="card white-bg mt-10 flex-md justify-around align-items-stretch" autocomplete="off">
	<div class="w-100 overflow-auto">
		
	</div>
</form>