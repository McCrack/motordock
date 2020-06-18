<div class="tiles-2-md mx-10">
	<div class="carousel p-2">
    	<div class="spinner">
    	@foreach($item->imageset as $i=>$img)
        	<input id="s-{{ $item->id }}-{{ $i }}" name="spinner-{{ $item->id }}" type="radio" hidden @if($loop->first) checked @endif>
        	<div class="slide">
            	@if(!$loop->first)
            	<label class="left spinner-btn" for="s-{{ $item->id }}-{{ ($i-1) }}">❮</label>
            	@endif
            	@if(!$loop->last)
            	<label class="right spinner-btn" for="s-{{ $item->id }}-{{ ($i+1) }}">❯</label>
            	@endif
            	<img
                data-src="{{ $img }}"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
				alt="{{ $item->make }} {{ $item->model }} @lang('dictionary.engine') Photo {{ ($i+1) }}"
				title="{{ $item->named }} photo {{ ($i+1) }}">
    	    </div>
	    @endforeach
    	</div>
    	<div class="carousel-items">
        	@foreach($item->imageset as $i=>$img)
        	<label for="s-{{ $item->id }}-{{ $i }}">
            	<img
                data-src="{{ $img }}"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                alt="{{ $item->make }} {{ $item->model }} @lang('dictionary.engine') preview {{ ($i+1) }}"
				title="Preview {{ ($i+1) }}">
        	</label>
        	@endforeach
    	</div>
	</div>
	<div class="py-3 px-1">
		<tt class="float-right font-size-16">Code: <b>{{ $item->id }}</b></tt>
		<p class="h2 my-5 text-bold orange-txt">{{ $item->make }} {{ $item->model }}</p>
		<h2 class="h2">{{ $item->named }}</h2>
		<div class="price text-center black-txt relative inline-block py-1 float-left">
	    	<div class="text-bold font-size-18">{{ $item->price }}</div>
    		<div class="red-txt text-medium font-size-16">inkl. Versand</div>
    		<!--<s class="silver-txt font-small">1800</s> <sup class="red-bg white-txt badge">10%</sup> € 1500-->
		</div>
		<div class="py-2 text-right">
	    	<input form="toCartform" name="ItemID" value="{{ $item->id }}" type="hidden">
    		<button form="toCartform" data-id="{{ $item->id }}" type="submit" class="toCart btn btn-lg btn-primary hover-danger-bg">@lang('dictionary.toCart')</button>
		</div>
		<div class="options py-1 font-size-13">
    		@foreach(($item->options ?? []) as $key => $val)
	    	<div class="flex justify-between border-top-tiny">
        		<span class="m-10">{{ $translate($key) }}:</span>
    	    	<span class="m-10">{{ $translate($val) }}</span>
	    	</div>
    		@endforeach
		</div>
	</div>
</div>