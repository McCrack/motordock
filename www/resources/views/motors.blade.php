<!DOCTYPE html>
<html prefix="og: https://ogp.me/ns#" lang="de">
<head>
	<!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TBSFBVN');</script>
    <!-- End Google Tag Manager -->

    <!-- Global site tag (gtag.js) - Google Ads: 1015277493 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-1015277493"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'AW-1015277493');
    </script>
    <!-- Event snippet for Purchase conversion page
    In your html page, add the snippet and call gtag_report_conversion when someone clicks on the chosen link or button. -->
    <script>
    function gtag_report_conversion(url) {
        var callback = function () {
            if (typeof(url) != 'undefined') {
                window.location = url;
            }
        };
        gtag('event', 'conversion', {
            'send_to': 'AW-1015277493/khFnCIKu5rUBELXPj-QD',
            'transaction_id': '',
            'event_callback': callback
        });
        return false;
    }
    </script>

	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes"/>
	<meta name="apple-mobile-web-app-capable" content="yes"/>
	<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
	<meta name="mobile-web-app-capable" content="yes"/>
	<meta name="csrf-token" content="{{ csrf_token() }}"/>
	
	<title>{{ $title }} — im Motordock Autoteile Onlineshop</title>
	<meta name="description" content="{{ $description }}"/>
	<link rel="canonical" href="{{ url()->current() }}">

	<meta property="og:type" content="website"/>
	<meta property="og:site_name" content="MOTORDOCK"/>
	<meta property="og:url" content="{{ url()->current() }}"/>

	<meta property="og:title" content="{{ $title }}"/>
	<meta property="og:description" content="{{ $description }}"/>

	<meta property="og:image" content="{{ $motor->picture }}"/>
	<meta property="og:locale" content="de_DE"/>

	<link rel=manifest href="/manifest.json">
	<link rel="icon" type="image/png" sizes="16x16" href="/img/favicons/favicon-16x16.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/img/favicons/favicon-32x32.png">

	<meta name=msapplication-TileColor content="black"/>
	<meta name=theme-color content="black"/>

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

    <link rel="preload" href="{{ asset('css/components.css') }}" as="style">
    <link rel="preload" href="/js/C-BBLe.js" as="script">

    <style>
	@import url('{{ asset('css/motors.css') }}');
	@import url('{{ asset('css/components.css') }}');
	</style>

	<script defer src="/js/C-BBLe.js?6"></script>
	<script defer src="/js/main.js"></script>

	<!--~~~ MICRODATA ~~~-->
    @foreach($microdata as $schema)
    <script type="application/ld+json">{!! stripcslashes(json_encode($schema, JSON_UNESCAPED_UNICODE)) !!}</script>
    @endforeach
    <script type="application/ld+json">{!! stripcslashes(json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE)) !!}</script>
</head>
<body>

	<!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TBSFBVN"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

	<div class="topbar flex align-items-center justify-between sticky top z-index-3 shadow-y main-bg-dark">
		<a href="/" class="text-bold font-large px-2">
			<img
			src="/img/logo/white.png"
			width="128" 
			alt="Motordock Logo"
			title="Zur Homepage">
		</a>
		<nav class="navbar px-1 border-left-tiny flex-grow display-none-sm">
			@foreach($map['static'] as $itm)
				@if($itm['published']=="Published")		
				<a href="/{{ $itm['slug'] }}" class="mx-5 font-size-13 light-txt hover-blue-txt">{{ $itm['header'] }}</a>
				@endif
			@endforeach
		</nav>
		<div class="toolbar pr-1">
			<label for="rightbar-toggler" class="tool cart-btn red-txt" data-amount="0">
				<svg class="w-52" viewBox="0 -40 580 580"><use fill="#CCC" xlink:href="/img/symbols.svg#cart"></svg>
				<script>
				var CART = JSON.parse( window.localStorage.cart || '{}' );
				(function(btn){
					btn.dataset.amount = Object.keys(CART).length;
				})(document.currentScript.parentNode)
				</script>
			</label>
			<!--
			<label for="leftbar-toggler" class="tool display-none-md">
				<span class="hamburger">
					<span></span>
					<span></span>
				</span>
			</label>
		-->
		</div>
	</div>
	<main class="white-bg">
		@yield('main')
		
		<input id="rightbar-toggler" name="cart" form="pop-up-manager" class="toggler" type="checkbox" hidden autocomplete="off">
		<aside class="cartbar fixed bottom right overflow-x-hidden overflow-y-auto border-left-tiny white-bg">
			<div class="font-large text-bold m-10 mt-20">
				<!--<span class="icon-shopping-cart red-txt"></span> @lang('dictionary.my-cart')-->
                <svg class="w-48 valign-middle" viewBox="-100 0 640 640"><use fill="#B36" xlink:href="/img/symbols.svg#cart"></svg>
                @lang('dictionary.my-cart')
			</div>
			<div class="cart sticky top">
				<p class="h2 silver-txt text-center py-4 text-tiny">@lang('cart-empty')</p>
			</div>
			<div class="mt-20 text-center">
				<label for="order-toggler" class="order-btn disabled btn btn-lg btn-primary">@lang('dictionary.create-order')</label>
			</div>
		</aside>
	</main>
	<!-- GDPR -->
	@include("components.gdpr")

	<!-- Footer -->
	@include("components.footer")

	<!--~~~ POP-UPs ~~~-->
    <form id="pop-up-manager" class="d-none">
        <script> var PopupManager = document.currentScript.parentNode; </script>
    </form>

	<!-- Order Form -->

    <input id="order-toggler" name="orderControl" class="toggler" form="pop-up-manager" type="checkbox" hidden autocomplete="off">
    <div class="pop-up fixed z-index-5">
		@include("components.order")
	</div>


	<!-- Callback Form -->

    <input id="callback-toggler" name="callbackControl" class="toggler" form="pop-up-manager" type="checkbox" hidden autocomplete="off">
    <div class="pop-up fixed z-index-5">
        @include("components.callback")
    </div>


    <!-- Message -->
    <input id="message-toggler" name="messageControl" class="toggler" form="pop-up-manager" type="checkbox" hidden autocomplete="off">
    <div class="pop-up fixed z-index-5">

    </div>

    <script>
    for(var key in PopupManager.elements){
        if(PopupManager.elements.hasOwnProperty(key)){
            PopupManager.elements[key].oninput = function(event){
                var checked = event.target.checked;
                PopupManager.reset();
                event.target.checked = checked;
            }
        }
    }
	PopupManager.cart.addEventListener("change", function(){
		if(PopupManager.cart.checked) refreshCart();
	});
    </script>

    <script>
	document.addEventListener('DOMContentLoaded', function(){
        
        var header = document.querySelector('h1');
        var words = header.textContent.split(/\s/);
        	words[0] = doc.create("span", {class: "white-txt"}, words[0]).outerHTML
        header.innerHTML = words.join(" ");

        var scroller = new Scroller();
        document.querySelectorAll("source[data-src]").forEach(function(pic){
            scroller.push(pic, -80, function(obj){
                obj.srcset = obj.dataset.src;
            });
        });
        document.querySelectorAll("img[data-src]").forEach(function(img){
            scroller.push(img, -80, function(obj){
                obj.src = obj.dataset.src;
            });
        });
        scroller.action();

        var catalog = document.querySelector(".catalog");
  		catalog.querySelectorAll("button.toCart").forEach(function(btn){
			btn.onclick = function(event){
				event.preventDefault();
				addToCart(btn.dataset.id, btn.dataset.price);
			}
		})
	});
	</script>

</body>
</html>