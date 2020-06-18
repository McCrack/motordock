@extends('index')

@section('title'){{ $title }}@endsection

@section('cover')
<div class="cover black-bg font-none">
    <img
        data-src="{{ $page->preview }}"
        src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
        class="fit-cover w-100"
        alt="Deckungskategorie {{ $category->name }}">
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
        <span class="h3 light-txt">↓</span>
    </div>
</div>
@endsection

@section('bar')
<nav class="breadcrumbs">
    @foreach($breadcrumbs['itemListElement'] as $crumb)
    <a class="hover-blue-txt" href="{{ $crumb['item'] }}">{{ $crumb['name'] }}</a>
    @endforeach
</nav>
@endsection


@section('main')
<style>
.items > li > label > input:checked + span{
    color: black;
    cursor: default;
    font-weight: bold;
    position: relative;
    text-decoration: underline;
}
.items > li > label > input:checked + span::before{
    content: "❮";
    left: -30px;
    position: absolute;
}
.items > li:nth-child(odd){
    background-color: #F5F5F5;
}
</style>
<div class="wrapper white-bg py-2">
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
        <div class="px-5">
        @isset($item)
            <div id="item-data">
            @include('components.item')
            </div>
            <form id="toCartform" class="text-right">
                <script>
                (function(form){
                    form.onsubmit = function(event){
                        event.preventDefault();
                        addToCart(form.ItemID.value, 1);
                    }
                })(document.currentScript.parentNode)
                </script>
            </form>
        @else
            <p class="h1 text-regular pt-5 pb-1 text-bold text-center">
                @lang('dictionary.nothing-found')
                <br><br>
                <a class="dark-txt text-medium hover-black-txt" href="javascript:history.back()">
                    <span class="hot-btn rounded-50 btn-primary valign-middle">❮</span>
                    @lang('dictionary.back')
                </a>
            </p>
        @endisset
        </div>
        <div class="px-3 pt-1">
            @if($catalog->count())
            <a class="black-txt text-medium font-large float-right hover-blue-txt" href="/{{ $item->category_slug }}/{{ $brand->slug }}">
                <span class="hot-btn rounded-50 btn-light valign-middle text-center shadow-x">❮</span>
                @lang('dictionary.back')
            </a>
            <p class="m-5 mt-30 text-medium dark-txt">@lang('dictionary.inStock'):</p>
            <ul class="items font-size-13 pl-0 clear">
                @foreach($catalog as $itm)
                <li class="flex justify-between align-items-center p-1">
                    <label class="hover-blue-txt cursor-pointer">
                        <input type="radio" name="subitem" value="{{ $itm->id }}" @if($itm->id == $item->id) checked @endif hidden>
                        <span>{{ $itm->named }}</span>
                    </label>
                    <span class="nowrap">€ <b>{{ $itm->price }}</b></span>
                </li>
                @endforeach
                <script>
                (function(list){
                    list.onchange=function(event){
                        //document.querySelector("#toCartform").ItemID.value = event.target.value;
                        XHR.push({
                            method: "GET",
                            addressee: "/ajax/item/"+event.target.value,
                            onsuccess: function(response){
                                var container = document.querySelector("#item-data");
                                container.innerHTML = response;
                                container.querySelectorAll('img[data-src]').forEach(function(img) {
                                    img.src = img.dataset.src;
                                    img.onload = function() {
                                        img.removeAttribute('data-src');
                                    };
                                });

                                var path = location.pathname.split("/");
                                    path[4] = event.target.value;
                                window.history.pushState(null, "filters",  path.join("/"));
                            }
                        });
                    }
                })(document.currentScript.parentNode)
                </script>
            </ul>

            <article class="mt-30">
            {!! $page->content !!}
            </article>
            @else
            <div class="flex justify-center align-items-center flex-wrap flex-dir-col h-300">
                <p class="h2 text-bold text-medium">@lang('dictionary.did-not-find')?</p>
                <p class="h4 text-regular my-10">
                    @lang('dictionary.call-us')
                    <a href="tel:‎+4951194040914" class="text-bold font-large black-txt nowrap">‎+49 511-940-40914</a>
                </p>
                <p class="h4 text-regular">
                    @lang('dictionary.fill-out-callback-form')
                    <label for="callback-toggler" class="inline-block border-tiny btn-lg rounded-5 cursor-pointer">
                        @lang('dictionary.callback')
                    </label>
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection