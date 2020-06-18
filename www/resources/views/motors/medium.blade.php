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
			<h1 class="red-txt uppercase">@lang('dictionary.engine') {{ $motor->article }}</h1>
			<p class="light-txt mt-10 max-w-600 px-5 border-box text-center font-size-18">
				<b class="white-txt">{{ $motor->article }}</b> - {{ $motor->specifications['engine displacement'] }}
				{{ $translate("{$motor->specifications['fuel type']} engine") }}
				mit <span class="nowrap">{{ ($motor->specifications['power'] ?? null) }}</span>
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
		@if($agent->isMobile())
		<div class="pb-5 text-center">
			<div class="inline-block font-size-14 w-128 border-tiny rounded-5 px-1 pb-1 m-5">
				<svg class="w-100" viewBox="-80 -80 672 672"><use xlink:href="/img/symbols.svg#fuel"></svg>
				<div class="dark-txt">{{ $translate('fuel type') }}</div>
				<div class="text-bold">{{ $translate($motor->fuel) }}</div>
			</div>
			
			<div class="inline-block font-size-14 w-128 border-tiny rounded-5 px-1 pb-1 m-5">
				<svg class="w-100" viewBox="-50 -50 612 612"><use xlink:href="/img/symbols.svg#torque"></svg>
				<div class="dark-txt">{{ $translate('torque') }}</div>
				<div class="text-bold nowrap">{{ $motor->torque }}</div>
			</div>

			<div class="inline-block font-size-14 w-128 border-tiny rounded-5 px-1 pb-1 m-5">
				<svg class="w-100" viewBox="0 0 512 512"><use xlink:href="/img/symbols.svg#power"></svg>
				<div class="dark-txt">{{ $translate('power') }}</div>
				<div class="text-bold">{{ $translate($motor->power, true) }}</div>
			</div>
			
			<div class="inline-block font-size-14 w-128 border-tiny rounded-5 px-1 pb-1 m-5">
				<svg class="w-100" viewBox="0 0 512 512"><use xlink:href="/img/symbols.svg#displacement"></svg>
				<div class="dark-txt">{{ $translate('engine displacement') }}</div>
				<div class="text-bold">{{ $motor->capacity }}</div>
			</div>
		</div>

		<p class="text-bold font-size-20 px-2 mt-20">Der {{ $motor->article }}-Motor ist in den folgenden Fahrzeugen verbaut:</p>
		<div class="text-regular font-size-15 py-1 px-2">
			<div class="my-5">{!! implode(',</div> <div class="my-5">', $motor->compatibility) !!}</div>
		</div>
			
		<img
		src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
		data-src="{{ $motor->picture }}"
		class="mt-20"
		alt="Engine {{ $motor->article }}"
		title="Motor {{ $motor->article }}">

		<h2 class="px-2 my-10">Technische Eigenschaften</h2>
		<table class="mb-20 font-size-14 text-regular" width="100%" cellpadding="6" cellspacing="0">
			<tbody>
				@foreach($motor->specifications as $key => $val)
				<tr>
					<td class="px-3">{{ $translate($key) }}</td>
					<td class="px-3">{{ $translate($val, true) }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		
		@else
		<div class="flex justify-around align-items-center p-5 text-center container">
			<div class="font-size-14 w-128 border-tiny rounded-5 px-1 pb-1">
				
				<svg class="w-100" viewBox="-80 -80 672 672"><use xlink:href="/img/symbols.svg#fuel"></svg>
				<div class="dark-txt">{{ $translate('fuel type') }}</div>
				<div class="text-bold capitalize">{{ $translate($motor->fuel) }}</div>
			</div>
			
			<div class="font-size-14 w-128 border-tiny rounded-5 px-1 pb-1">
				
				<svg class="w-100" viewBox="-50 -50 612 612"><use xlink:href="/img/symbols.svg#torque"></svg>
				<div class="dark-txt">{{ $translate('torque') }}</div>
				<div class="text-bold nowrap">{{ $motor->torque }}</div>
			</div>

			<div class="font-size-14 w-128 border-tiny rounded-5 px-1 pb-1">
				
				<svg class="w-100" viewBox="0 0 512 512"><use xlink:href="/img/symbols.svg#power"></svg>
				<div class="dark-txt">{{ $translate('power') }}</div>
				<div class="text-bold">{{ $translate($motor->power, true) }}</div>
			</div>
			
			<div class="font-size-14 w-128 border-tiny rounded-5 px-1 pb-1">
				
				<svg class="w-100" viewBox="0 0 512 512"><use xlink:href="/img/symbols.svg#displacement"></svg>
				<div class="dark-txt">{{ $translate('engine displacement') }}</div>
				<div class="text-bold">{{ $motor->capacity }}</div>
			</div>
		</div>
		<div class="container h-300 flex-md flex-wrap flex-dir-col justify-center align-items-start px-1">
			<img src="{{ $motor->picture }}" width="250" class="fit-contain flex-basis mr-20">

			<h2 class="font-size-24">Technische Eigenschaften</h2>
			<ul class="columns-3-md font-size-14 text-regular pl-0 m-0 max-w-800" type="none">
				@foreach($motor->specifications as $key => $val)
				<li class="inline-block w-100 my-4">
					{{ $translate($key) }}:
					<span class="text-bold">{{ $translate($val, true) }}</span>
				</li>
				@endforeach
			</ul>
		</div>
		<div class="container pb-4">
			<p class="text-bold font-size-20 my-5">Der {{ $motor->article }}-Motor ist in den folgenden Fahrzeugen verbaut:</p>
			<div class="text-regular font-size-15 mb-10">
				<span class="m-4">{!! implode(',</span> <span class="my-5 nowrap mx-4">', $motor->compatibility) !!}</span>
			</div>
		</div>
		@endif

		<div class="container catalog">
			<h2 class="black-bg p-1 font-size-18 text-medium light-txt">@lang('dictionary.buy-engine') {{ $motor->article }}</h2>
			@isset($item)
				<div class="showcase">
				<div id="item-data">
				@include('motors.components.item')
				</div>
			</div>
			@else
				<div class="font-size-15 text-regular my-10 ml-5"><span class="red-txt">●</span> Die Preise beinhalten die Lieferung</div>
				@include('motors.components.catalog')
			@endisset
		</div>
	</div>
</div>
@endsection
