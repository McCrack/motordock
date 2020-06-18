@foreach($catalog as $item)
<li class="snippet border-tiny rounded-3">
	<div class="preview">
		<img
			data-src="{{ $item->preview }}"
			src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
			alt="{{ $category->name }} {{ $item->brand }} {{ $item->model }} - {{ $item->id }}"
			title="{{ $item->named }}">
	</div>
	<p class="text-bold silver-txt">{{ $item->brand }}</p>
	<p class="named text-medium">{{ $item->named }}</p>
	<p class="price text-tiny text-right mr-2">€ <span class="text-bold">{{ $item->price }}</span></p>
	<p class="snippet-footer flex justify-between">
		<a class="font-size-15 blue-txt hover-red-txt" href="/{{ $item->slug }}">Detailliert <span class="black-txt">→</span></a>
		<button data-id="{{ $item->id }}" data-price="{{ $item->price }}" class="toCart btn btn-sm btn-primary">@lang('dictionary.toCart')</button>
	</p>
</li>
@endforeach

@if($catalog->total() > $catalog->count())
    {{$catalog->links()}}
@endif