@foreach($catalog as $item)
<div class="snippet" data-id="{{ $item->id }}" data-price="{{ $item->price }}">
	<div class="preview">
		<img src="{{ $item->preview }}" alt="">
		<!--<div class="discount">5%</div>-->
	</div>
	<div>
		<button data-id="{{ $item->id }}" class="drop-btn cursor-pointer padding-0 border-none transparent-bg silver-txt float-right">✕</button>
		<span class="silver-txt">{{ $item->maker }}</span>
	</div>
	<div class="named">
		{{ $item->named }}
	</div>
	<div class="price">
		<!-- <s class="silver-txt">1800</s> -->
		€ <b>{{ $item->price }}</b>
	</div>
</div>
@endforeach