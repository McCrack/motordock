<div class="accordion white-bg">
	@foreach($store as $item)
	<input id="atab-{{ $item->id }}" type="radio" name="catalog" autocomplete="off" value="{{ $item->id }}" class="catalog-item" hidden>
	@if($agent->isMobile())
	<label
	for="atab-{{ $item->id }}"
	class="item font-size-13 flex flex-wrap align-start justify-between flex-dir-col h-120 py-2 border-top-tiny">
		<img
		src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
		data-src="{{ $item->preview }}"
		alt="{{ $item->make }} {{ $item->model }} @lang('dictionary.engine')"
		title="{{ $item->named }}"
		width="134"
		hspace="8"
		class="flex-basis fit-cover rounded-3">
		<span class="text-bold dark-txt mt-2 font-size-14">{{ $item->make }}</span>
		<span class="overflow-hidden dark-txt text-overflow-llipsis text-medium font-size-12" style="width:calc(100% - 160px)">{{ $item->named }}</span>
		<span class="">
			<span class="cursor-pointer font-size-14 blue-txt hover-black-txt hover-underline">
				Detailliert
				<span class="dark-txt">→</span>
			</span>
			<span class="price float-right text-bold">{{ $item->price }}</span>
		</span>
		<button data-id="{{ $item->id }}" data-price="{{ $item->price }}" class="toCart btn btn-sm btn-primary">@lang('dictionary.toCart')	</button>
	</label>
	@else
	<label for="atab-{{ $item->id }}" class="item tab-btn flex justify-between align-items-center">
		<img
		src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
		data-src="{{ $item->preview }}"
		class="w-64 m-10"
		alt="{{ $item->make }} {{ $item->model }} @lang('dictionary.engine')"
		title="{{ $item->named }}">
		<span class="text-bold w-128 text-center light-txt">{{ $item->make }}</span>
		<span class="font-size-14 dark-txt max-w-400">{{ $item->named }}</span>
		<span class="price w-128 black-txt text-tiny text-right">€ <span class="text-bold">{{ $item->price }}</span></span>
		<span class="cursor-pointer font-size-14 blue-txt hover-black-txt hover-underline">
			Detailliert
			<span class="dark-txt">→</span>
		</span>
		<button
			data-id="{{ $item->id }}"
			data-price="{{ $item->price }}"
			class="toCart btn btn-sm btn-primary hover-danger-bg mr-10">@lang('dictionary.toCart')
		</button>
		
	</label>
	@endif
	<div class="tab white-bg">
		<div class="tab-body level mb-10">
			@include('motors.components.item')
		</div>
	</div>
	@endforeach
	<br class="clear-left">
</div>