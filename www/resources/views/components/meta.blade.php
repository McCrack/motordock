<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
<meta name="mobile-web-app-capable" content="yes"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
	

<link rel="canonical" href="{{ url()->current() }}">

@foreach($meta as $name => $value)
<meta name="{{ $name }}" content="{{ $value }}"/>
@endforeach

@if(($catalog ?? false) && ($catalog instanceof \Illuminate\Pagination\LengthAwarePaginator))
@if($catalog->currentPage() > 1)
<link rel="prev" href="{{ $catalog->previousPageUrl() }}">
@endif
@if($catalog->currentPage() < $catalog->lastPage())
<link rel="next" href="{{ $catalog->nextPageUrl() }}">
@endif
@endif

<meta property="og:type" content="website"/>
<meta property="og:site_name" content="MOTORDOCK"/>
<meta property="og:url" content="{{ url()->current() }}"/>

<meta property="og:title" content="@yield('title')"/>
<meta property="og:description" content="{!! $meta['description'] !!}"/>

<meta property="og:image" content="{{ $page->preview }}"/>
<meta property="og:locale" content="de_DE"/>

<link rel=manifest href="/manifest.json">
<link rel="icon" type="image/png" sizes="16x16" href="/img/favicons/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/img/favicons/favicon-32x32.png">

<meta name=msapplication-TileColor content="#152532"/>
<meta name=theme-color content="#152532"/>

<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-60x60.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-72x72.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-76x76.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-114x114.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-120x120.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-144x144.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-152x152.png">
<link rel=apple-touch-icon href="/img/favicons/apple-touch-icon-180x180.png">

<meta name=msapplication-config content="/browserconfig.xml"/>