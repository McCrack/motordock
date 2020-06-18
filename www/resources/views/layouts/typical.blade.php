@extends('index')

@section('title'){{ $page->header }}@endsection

@section('cover')
<div class="cover display-none">

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


@section('main')
<div class="wrapper p-1">
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
	<article class="text-container container py-5 px-3 my-20 white-bg shadow">
		{!! $page->content !!}
	</article>
</div>
@endsection