@foreach($items as $item)
<tr>
	<td>
		@if($item->itemId)
		<input 
			name="item"
			type="checkbox"
			value="{{ $item->itemId }}"
			data-seller="{{ $item->sellerInfo['sellerUserName'] }}"
			data-store="{{ $item->storeInfo['storeName'] ?? '' }}"
			data-market="{{ $item->globalId }}"
			data-category="{{ $category }}">
		@else
		<span class="font-size-12 green-txt">Already</span>
		@endif
	</td>
	<td class="w-64">
		<img
			src="{{ $item->galleryURL ?? '/images/icons/noimage.png' }}"
			class="fit-contain h-52 w-64 black-bg">
	</td>
	<td class="text-medium" align="left">
		{{ $item->title }}
		<a class="blue-txt nowrap text-regular" href="{{ $item->viewItemURL}} " target="_blank">→ Source</a>
	</td>
	<td class="nowrap">
		<b>{{ $item->sellingStatus['currentPrice']['__value__'] }}</b>
		<span>{{ $item->sellingStatus['currentPrice']['@currencyId'] }}</span>
	</td>
	<td>
		€  {{ $item->price }}
	</td>
	<td>{{ $item->condition['conditionDisplayName'] }}</td>
	<td>
		<div class="main-txt">{{ $item->storeInfo['storeName'] ?? "" }}</div>
		<div class="dark-txt">{{ $item->sellerInfo['sellerUserName'] ?? "" }}</div>
	</td>
</tr>
@endforeach

@if($items->total > 1)
<tr class="white-bg border-top-tiny">
	<td colspan="6" align="left">
		<div class="p-1 inline-block">
			<div class="inline-block valign-middle mr-5">
				Page:
				<output form="search" name="offset" class="text-bold">{{ $items->current }}</output> 
				/ {{ $items->total }}
			</div>
			@if($items->current > 1)
			<a title="PREVIOUS" class="btn btn-sm btn-primary" href="javascript:document.forms['search'].reload({{ $items->current - 1 }})">❮</a>
			@else
			<a title="PREVIOUS" class="btn btn-sm btn-primary disabled">❮</a>
			@endif
			@if($items->current < $items->total)
			<a title="NEXT" class="btn btn-sm btn-primary" href="javascript:document.forms['search'].reload({{ 	$items->current + 1 }})">❯</a>
			@else
			<a title="NEXT" class="btn btn-sm btn-primary disabled">❯</a>
			@endif
		</div>
	</td>
	<td align="right">
		<button class="btn btn-primary btn-lg mr-10" onclick="stepTwo(event)">Import »</button>
	</td>
</tr>
@endif