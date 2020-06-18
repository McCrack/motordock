@extends('motors')

@section('main')
<div class="inline shadow-y">
	<section class="motor-cover relative white-bg vh-50">
		<picture>
			<source type="image/webp" srcset="https://motordock.de/data/covers/engine.webp">
			<source type="image/jpeg" srcset="https://motordock.de/data/covers/engine.jpg">
			<img
			src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
			data-src="https://motordock.de/data/covers/engine.jpg"
			class="fit-cover w-100 h-100 black-bg"
			alt="Deckungskategorie Motor">
		</picture>
		<div class="substrate z-index-2 absolute top left w-100 h-100 flex justify-center align-items-center flex-dir-col flex-wrap">
			<h1 class="red-txt uppercase">{{ $translate('engine') }} {{ $motor->article }}</h1>
			<p class="light-txt mt-10 max-w-600 px-5 border-box text-center font-size-20">
			<b class="white-txt">{{ $motor->article }}</b> - {{ $motor->specifications['capacity'] }}
			{{ $translate("{$motor->specifications['fuel']} engine") }}
			mit {{ $motor->specifications['power'] }}
			</p>
		</div>
	</section>
</div>

<div class="wrapper">
	<aside class="sidebar leftbar light-bg display-none-md flex-sm flex-dir-col justify-end">
		<nav class="level">
			
		</nav>
		<nav class="level">
			
		</nav>
		<div class="p-5"></div>
	</aside>
	<div>
		<div class="flex justify-around align-items-center p-5 text-center container">
			<div class="font-size-14 w-86 border-tiny rounded-5 p-1">
				<div class="text-bold">{{ $translate($motor->specifications['fuel']) }}</div>
				<svg class="w-100" viewBox="-80 -80 672 672"><use xlink:href="/img/symbols.svg#fuel"></svg>
				<div class="dark-txt">{{ $translate('fuel type') }}</div>
			</div>

			<div class="font-size-14 w-128 mx-10 border-tiny rounded-5 p-1">
				<div class="text-bold">{{ $motor->specifications['power'] }}</div>
				<svg class="w-100" viewBox="0 0 512 512"><use xlink:href="/img/symbols.svg#power"></svg>
				<div class="dark-txt">{{ $translate('power') }}</div>
			</div>
			
			<div class="font-size-14 w-86 border-tiny rounded-5 p-1">
				<div class="text-bold">{{ $motor->specifications['capacity'] }}</div>
				<svg class="w-100" viewBox="0 0 512 512"><use xlink:href="/img/symbols.svg#displacement"></svg>
				<div class="dark-txt">{{ $translate('capacity') }}</div>
			</div>
		</div>
		@if($agent->isMobile())
		<p class="text-bold font-size-20 px-2 mt-20">Der {{ $motor->article }}-Motor ist in den folgenden Fahrzeugen verbaut:</p>
		<div class="text-regular font-size-15 py-1 px-2">
			<div class="my-5">{!! implode(',</div> <div class="my-5">', $motor->compatibility) !!}</div>
		</div>
			
		<div class="text-center">
			<img
			src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
			data-src="{{ $motor->picture }}"
			class="mt-20"
			alt="Engine {{ $motor->article }}"
			title="Motor {{ $motor->article }}">
		</div>

		<h2 class="px-2 my-10">Technische Eigenschaften</h2>
		<table class="mb-20 font-size-14 text-regular" width="100%" cellpadding="6" cellspacing="0">
			<tbody>
				@foreach($motor->specifications as $key => $val)
				<tr>
					<td class="px-3">{{ $translate($key) }}</td>
					<td class="px-3">{{ $val }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@else
		<div class="container h-400 flex-md flex-wrap flex-dir-col justify-center align-items-start p-1">
			<img src="{{ $motor->picture }}" width="310" class="fit-contain flex-basis mr-20">
			
			<div class="max-w-600">
				<p class="text-bold font-size-20 my-5">Der {{ $motor->article }}-Motor ist in den folgenden Fahrzeugen verbaut:</p>
				<div class="text-regular font-size-15 mb-10 max-w-700">
					<span class="m-4">{!! implode(',</span> <span class="my-5 nowrap mx-4">', $motor->compatibility) !!}</span>
				</div>
			</div>

			<h2 class="font-size-24">Technische Daten / Datenblatt</h2>
			<ul class="columns-3-md font-size-14 text-regular pl-0 m-0 max-w-800" type="none">
				@foreach($motor->specifications as $key => $val)
				<li class="inline-block w-100 my-4">{{ $translate($key) }}: <span class="text-bold">{{ $val }}</span></li>
				@endforeach
			</ul>
		</div>
		@endif

		<div class="container mt-20">
			<p class="black-bg p-1 light-txt">@lang('dictionary.inStock')</p>
			@isset($item)
				@include('components.item')
			@else
				product list
				@foreach($store as $item)

				@endforeach
			@endisset
		</div>
	</div>
</div>
@endsection
