<tt class="float-right font-size-16">Code: <b>{{ $item->id }}</b></tt>
<p class="h1 my-5 text-bold orange-txt">{{ $item->make }} {{ $item->model }}</p>
<h1 class="h2">{{ $item->named }}</h1>
<div class="h2 price text-center black-txt relative inline-block py-1 float-left">
    <div class="text-medium">{{ $item->price }}</div>
    <div class="red-txt text-bold font-size-16">inkl. Versand</div>
    <!--<s class="silver-txt font-small">1800</s> <sup class="red-bg white-txt badge">10%</sup> € 1500-->
</div>
<div class="py-1 text-right">
    <input form="toCartform" name="ItemID" value="{{ $item->id }}" type="hidden">
    <button form="toCartform" type="submit" class="btn btn-lg btn-primary">@lang('dictionary.toCart')</button>
</div>
<div class="options py-1 font-size-13">
    @foreach(($item->options ?? []) as $key => $val)
    <div class="flex justify-between border-top-tiny">
        <span class="mx-10">{{ $translate($key) }}:</span>
        <span class="mx-10">{{ $translate($val) }}</span>
    </div>
    @endforeach
</div>
<section class="carousel">
    <div class="spinner">
    @foreach($item->imageset as $i=>$img)
        <input id="s-{{ $i }}" name="spinner-58" type="radio" hidden @if($loop->first) checked @endif>
        <div class="slide">
            @if(!$loop->first)
            <label class="left spinner-btn" for="s-{{ ($i-1) }}">❮</label>
            @endif
            @if(!$loop->last)
            <label class="right spinner-btn" for="s-{{ ($i+1) }}">❯</label>
            @endif
            <img
                data-src="{{ $img }}"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                alt="{{ $title }} photo {{ ($i+1) }}"
                title="{{ $item->named }} photo {{ ($i+1) }}">
        </div>
    @endforeach
    </div>
    <div class="carousel-items">
        @foreach($item->imageset as $i=>$img)
        <label for="s-{{ $i }}">
            <img
                data-src="{{ $img }}"
                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                alt="{{ $title }} preview {{ ($i+1) }}"
                title="Preview {{ ($i+1) }}">
        </label>
        @endforeach
    </div>
</section>