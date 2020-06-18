@extends('index')

@section('title')@lang('dictionary.vehicle-spare')@endsection

@section('cover')
<section class="flex-md justify-around align-items-center py-5 mx-20">
	<img src="/img/home/motor.jpg" class="w-100 max-w-500" alt="Titelseite">
	<div class="max-w-600">
		<img width="200" data-src="/img/logo/black.png" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Motordock">
		<h3 class="header text-tiny">Komplette Motoren</h3>
		<div class="columns-4-md columns-2 font-size-15 mx-10 my-20">
			<a href="/komplette-motoren/audi" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Audi</a>
			<a href="/komplette-motoren/bmw" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">BMW</a>
			<a href="/komplette-motoren/citroen" class="inline-block mt-5 w-100 dark-txt hover-underline hover-black-txt">Citroen</a>
			<a href="/komplette-motoren/fiat" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Fiat</a>
			<a href="/komplette-motoren/ford" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Ford</a>
			<a href="/komplette-motoren/jaguar" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Jaguar</a>
			<a href="/komplette-motoren/land-rover" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Land Rover</a>
			<a href="/komplette-motoren/nissan" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Nissan</a>
			<a href="/komplette-motoren/mazda" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Mazda</a>
			<a href="/komplette-motoren/mercedes-benz" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Mercedes-benz</a>
			<a href="/komplette-motoren/opel" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Opel</a>
			<a href="/komplette-motoren/peugeot" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Peugeot</a>
			<a href="/komplette-motoren/renault" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Renault</a>
			<a href="/komplette-motoren/volkswagen" class="inline-block w-100 mt-5 dark-txt hover-underline hover-black-txt">Volkswagen</a>
		</div>
		<div class="mx-10 float-right-md pr-3">
			<a href="/komplette-motoren" class="inline-block w-100 mr-10 black-txt text-bold hover-blue-txt">Alle Marken ❯</a>
		</div>
	</div>
</section>
@endsection

@section('bar')
<div></div>


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
	<div class="flex-md justify-between align-items-center main-bg-dark p-3 white-txt">
		<article class="pl-2">
			<h2 class="font-size-18 text-bold my-5">Verwendung von Marken</h2>
			<p class="max-w-600 font-size-14 text-regular">
				Alle in unseren Bildern und Texten verwendeten Herstellernamen, -symbole und -beschreibungen dienen ausschließlich zu Identifikationszwecken. Es wird weder vermutet noch impliziert, dass ein von motordock.de verkaufter Artikel ein Produkt ist, das von einem auf dieser Seite gezeigten Fahrzeughersteller autorisiert wurde oder in irgendeiner Weise damit in Verbindung steht.
			</p>
		</article>
		<div class="light-txt font-size-15 pr-5 pl-2 mt-20">
			<div>@lang('dictionary.call-us')</div>
			<a href="tel:‎+4951194040914" class="text-bold white-txt font-size-24 white-txt nowrap">‎+49 511-940-40914</a>
		</div>
	</div>
	<div class="py-5 white-bg">
		<div>
			@if($agent->isMobile())
			@if($agent->isFirefox() || $agent->isChrome())
			<object type="image/svg+xml" data="/img/vehicles/lancer/body-webp-mob.svg" class="w-100">Karosserieteile</object>
			@else
			<object type="image/svg+xml" data="/img/vehicles/lancer/body-mob.svg" class="w-100">Karosserieteile</object>
			@endif
			@else
			@if($agent->isFirefox() || $agent->isChrome())
			<object type="image/svg+xml" data="/img/vehicles/lancer/body-webp.svg" class="w-100">Karosserieteile</object>
			@else
			<object type="image/svg+xml" data="/img/vehicles/lancer/body.svg" class="w-100">Karosserieteile</object>
			@endif
			@endif
		</div>
	</div>
	<nav class="p-5">
		<p class="header mb-20">Alle Kategorie</p>
		@foreach($catTree[1] as $part)
		<a href="{{ $part['slug'] }}" class="my-10 font-size-24 black-txt text-bold hover-blue-txt">{{ $part['name'] }}</a>
		<div class="columns-2-md columns-4-lg p-1">
			@foreach($catTree[$part['id']] as $level_1)
			<div class="inline-block w-100">
				<a href="/{{ $level_1['slug'] }}" class="mt-10 black-txt text-bold font-size-18 block hover-blue-txt">
					{{ $level_1['name'] }}
				</a>
				@if(isset($catTree[$level_1['id']]))
				<div class="level">
					@foreach($catTree[$level_1['id']] as $level_2)
					<a href="/{{ $level_2['slug'] }}" class="mt-10 text-regular font-size-16 hover-blue-txt">
						{{ $level_2['name'] }}
					</a>
					@if(isset($catTree[$level_2['id']]))
					<div class="level">
						@foreach($catTree[$level_2['id']] as $level_3)
						<a href="/{{ $level_3['slug'] }}" class="mt-10 text-light font-size-15 hover-blue-txt">
							{{ $level_3['name'] }}
						</a>
						@endforeach
					</div>
					@endif
					@endforeach
				</div>
				@endif
			</div>
			@endforeach
		</div>
		@endforeach
	</nav>
	<div class="flex-md p-5 align-items-center justify-around white-bg border-top-tiny">
		<article class="max-w-700 pr-5 dark-txt font-size-14 mb-10">
			{!! $page->content !!}
		</article>
		<img width="480" data-src="/img/home/lagerhaus-2.jpg" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Motorenlager" title="Motorlager" class="rounded-5 display-none-sm">
	</div>
</div>
@endsection