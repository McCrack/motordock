@extends('index')

@section('title'){{ $title }}@endsection

@section('cover')
<div class="cover black-bg font-none">
	<picture>
		@isset($page->cover['webp'])
		<source type="image/webp" srcset="{{ $page->cover['webp'] }}">
		@endisset
		@isset($page->cover['jpg'])
		<source type="image/jpeg" srcset="{{ $page->cover['jpg'] }}">
		@endisset
		<img
		src="{{ $page->cover['default'] }}"
		class="fit-cover w-100"
		alt="Deckungskategorie {{ $title }}">
	</picture>
	<div class="substrate z-index-2 flex align-items-center justify-between flex-dir-col text-center p-2">
		<div class="white-txt">
			<p class="h3 light-txt">@lang('dictionary.working-hours')</p>
			<p>@lang('dictionary.Mo-Fr'): 9:00 - 18:00. @lang('dictionary.Sa-Su-closed')</p>
		</div>
		<div>
			<p class="h1 text-regular white-txt">@lang('dictionary.did-not-find')?</p>
			<p class="h3 text-regular my-5 light-txt">
				@lang('dictionary.call-us')
				<a href="tel:‎+4951194040914" class="text-bold white-txt font-large white-txt nowrap">‎+49 511-940-40914</a>
			</p>
			<p class="h3 text-regular light-txt">
				@lang('dictionary.fill-out-callback-form')
				<label for="callback-toggler" class="inline-block border-tiny btn-lg rounded-5 white-txt cursor-pointer">
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

@section('leftbar')
<div class="tabs p-1">
	<input type="radio" name="sidebar-tabs" hidden checked autocomplete="on">
	<label class="tab-btn">@lang('dictionary.categories')</label>
	<div class="tab">
		<nav class="tab-body p-1">
			@include("components.categories")
		</nav>
	</div>
	<br class="clear-left">
</div>
@endsection

@section('main')
<div class="wrapper explorer">
	<aside class="sidebar leftbar light-bg">
		@yield('leftbar')
	</aside>
	<div>
		<div class="tabs py-1">
			<input id="tab-1" type="radio" name="filters-tabs" hidden checked autocomplete="on">
			<label for="tab-1" class="tab-btn">{{ $translate('brand') }}</label>
			<div class="tab">
				<div class="filterset tab-body p-1 text-center">
					@foreach($brands as $make)
					<a class="inline-block w-100 h-80 @if($make->slug == ($brand->slug ?? null)) selected @endif" href="/{{ $category->slug }}/{{ $make->slug }}">
						<svg viewBox="0 0 512 512" width="64"><use xlink:href="/img/symbols.svg#{{ $make->slug }}"/></svg>
						<div class="blue-txt">{{ $make->brand }}</div>
					</a>
					@endforeach
				</div>
			</div>
			@isset($brand)
			<input id="tab-2" type="radio" name="filters-tabs" hidden checked autocomplete="on">
			<label for="tab-2" class="tab-btn"></span>{{ $translate('model') }}</label>
			<div class="tab">
				<div class="filterset tab-body p-1 text-center">
					@foreach($lineups as $lineup)
					<a class="inline-block w-100 h-120" @if($lineup->cnt > 0) href="/{{ $category->slug }}/{{ $brand->slug }}/{{ $lineup->slug }}" @endif>
						<picture>
							@isset($lineup->images['webp'])
							<source
							type="image/webp"
							srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
							data-src="{{ $lineup->images['webp'] }}">
							@endisset
							<img
							data-src="{{ $lineup->image }}"
							src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
							class="h-72 fit-cover"
							alt="{{ $lineup->model }}"
							title="{{ $brand->brand }} {{ $lineup->model }}">
						</picture>
						<div class="black-txt font-size-14 text-bold">
							{{ $lineup->model }} <span class="dark-txt text-regular">[{{ $lineup->cnt }}]</span>
						</div>
						<div class="dark-txt font-size-12 text-regular">{{ $lineup->modifications }}</div>
					</a>
					@endforeach
				</div>
			</div>
			@endisset
			<br class="clear-left">
		</div>
		<div class="white-bg container shadow px-1">
			<p class="h2 px-2 pt-3 dark-txt">
				<span class="text-bold">{{ $catalog->total() }}</span>
				@lang('dictionary.items-in-category')
			</p>
			<h1 class="px-2 pb-1">{{ $title }}</h1>

			<ul class="catalog pl-0 mt-20">
				@include("components.catalog")
			</ul>
		</div>
		<div class="white-bg container shadow p-4">
			<article class="p-1 font-size-14 dark-txt max-w-800">
				{!! $category->content ?? null !!}
			</article>
		</div>
	</div>
</div>
@endsection
