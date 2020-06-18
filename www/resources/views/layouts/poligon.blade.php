@extends('index')

@section('title'){{ $title }}@endsection

@section('cover')
<div class="cover black-bg font-none">
	
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
	<label class="tab-btn"><span class="icon-flow-cascade dark-txt"></span> @lang('dictionary.categories')</label>
	<div class="tab">
		<nav class="tab-body p-1">
			
		</nav>
	</div>
	<br clear="left">
</div>
@endsection

@section('main')
<div class="wrapper">
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
	<div>
		
	</div>
</div>
@endsection
