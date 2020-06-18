@extends('index')

@section('title') {{ $title }} @endsection

@section('cover')
<div class="cover black-bg font-none">
    <img src="{{ $page->preview }}" class="fit-cover w-100" alt="Deckungskategorie {{ $category->name }}">
    <div class="substrate z-index-2 flex align-items-center justify-between flex-dir-col text-center p-2">
        <div class="white-txt">
            <p class="h3 light-txt">@lang('dictionary.working-hours')</p>
            <p>@lang('dictionary.Mo-Fr'): 9:00 - 18:00. @lang('dictionary.Sa-Su-closed')</p>
        </div>
        <div>
            <p class="h1 text-medium white-txt">@lang('dictionary.still-questions')</p>
            <p class="h4 text-regular my-5 light-txt">
                @lang('dictionary.call-us')
                <a href="tel:‎+4951194040914" class="text-bold white-txt font-large white-txt nowrap">‎+49 511-940-40914</a>
            </p>
            <p class="h4 text-regular light-txt">
                @lang('dictionary.fill-out-callback-form')
                <label for="callback-toggler" class="inline-block border-tiny btn-lg my-5 rounded-5 white-txt cursor-pointer">
                    @lang('dictionary.callback')
                </label>
            </p>
        </div>
        <span class="h3 icon-chevron-down light-txt"></span>
    </div>
</div>
@endsection

@section('bar')
<nav class="breadcrumbs">
    @foreach($breadcrumbs['itemListElement'] as $crumb)
    <a class="hover-blue-txt" href="{{ $crumb['item'] }}">{{ $crumb['name'] }}</a>
    @endforeach
</nav>
<!--
<form action="/search" method="GET" class="search-form h-32 text-right-md text-center-sm" autocomplete="off">
    <input type="search" name="query" placeholder="&#xe986;" class="shadow" required>
    <button class="orange-bg hover-primary-bg cursor-pointer">@lang('dictionary.find')</button>
</form>
-->
@endsection

@section('carousel')
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
            src="{{ $img }}"
            alt="{{ $title }} photo {{ ($i+1) }}"
            title="{{ $item->named }} photo {{ ($i+1) }}">
    </div>
    @endforeach
</div>
<div class="carousel-items">
    @foreach($item->imageset as $i=>$img)
    <label for="s-{{ $i }}">
        <img
            src="{{ $img }}"
            alt="{{ $title }} preview {{ ($i+1) }}"
            title="Preview {{ ($i+1) }}">
    </label>
    @endforeach
</div>
@endsection


@section('data')
<input name="ItemID" value="{{ $item->id }}" type="hidden">
<div class="h2 price text-center-sm red-txt">
    @lang('dictionary.sold')
</div>
<div class="pt-2">
    @foreach(($item->options ?? []) as $key => $val)
    <div>{{ $key }}: <b>{{ $val }}</b></div>
    @endforeach
</div>
<article class="py-1">
    <!-- Condition Description -->
</article>
<article>
    <!--<p class="text-bold my-5 font-large">Beschreibung</p>-->
    {!! $page->content !!}
</article>
@endsection

@section('main')
<div class="wrapper white-bg py-4">
    <aside class="sidebar leftbar light-bg display-none-md flex-sm flex-dir-col justify-end">
        <nav class="level">
            @foreach($map['static'] as $itm)
                @if($itm['published']=="Published")
                <a href="/{{ $itm['slug'] }}" class="block my-5 black-txt text-regular">{{ $itm['header'] }}</a>
                @endif
            @endforeach
        </nav>
        <nav class="level">
            @foreach($map['showcase'] as $itm)
                @if($itm['published']=="Published")
                <a href="/{{ $itm['slug'] }}" class="block my-5 black-txt text-medium">{{ $itm['header'] }}</a>
                @endif
            @endforeach
        </nav>
        <div class="p-5"></div>
    </aside>
    <div class="showcase">
        <header class="white-bg px-2 mt-20">
            <p class="h2 silver-txt">{{ $item->make }}</p>
            <h1>{{ $item->named }}</h1>
        </header>
        <section class="carousel white-bg p-3">
            @yield('carousel')
        </section>
        <form class="white-bg p-2">
            @yield('data')
        </form>
        <div class="compatibility">
            <table cellpadding="5" cellspacing="0" class="border-tiny">
                <thead>
                    <tr align="center" class="main-bg-dark white-txt font-size-14">
                        <td>Car Make</td>
                        <td>Model</td>
                        <td>Cars Year</td>
                        <td>Variant</td>
                        <td>Cars Type</td>
                        <td>BodyStyle</td>
                        <td>Engine</td>
                    </tr>
                </thead>
                <tbody class="font-size-13">
                    @foreach($compatibility['Car Make'] as $i => $cell)
                    <tr align="center">
                        <td>{{ $cell }}</td>
                        <td>{{ $compatibility['Model'][$i] }}</td>
                        <td>{{ $compatibility['Cars Year'][$i] }}</td>
                        <td>{{ $compatibility['Variant'][$i] }}</td>
                        <td>{{ $compatibility['Cars Type'][$i] }}</td>
                        <td>{{ $compatibility['BodyStyle'][$i] }}</td>
                        <td>{{ $compatibility['Engine'][$i] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection