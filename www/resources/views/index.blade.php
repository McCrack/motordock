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

    @include('components.meta')

	<title>@yield('title') — im Motordock Autoteile Onlineshop</title>

    <link rel="preload" href="{{ asset('css/components.css') }}" as="style">
    <link rel="preload" href="/js/C-BBLe.js" as="script">

    @include('components.styles')

	<script defer src="/js/C-BBLe.js?6"></script>
	<script defer src="/js/main.js?1"></script>

    <!--~~~ MICRODATA ~~~-->
    @foreach($page->microdata as $schema)
    <script type="application/ld+json">{!! stripcslashes(json_encode($schema, JSON_UNESCAPED_UNICODE)) !!}</script>
    @endforeach
    <script type="application/ld+json">{!! stripcslashes(json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE)) !!}</script>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TBSFBVN"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

	<input form="pop-up-manager" id="leftbar-toggler" class="toggler" type="checkbox" hidden autocomplete="off">
	<!-- Loft -->
	@include("components.loft")
	@yield('cover')
	<div class="main-bg-dark px-1 flex-md align-items-center justify-between">
		@yield('bar')
	</div>
	<!-- Page Layout -->
	<main class="light-bg">

		@yield('main')

		<input id="rightbar-toggler" name="cart" form="pop-up-manager" class="toggler" type="checkbox" hidden autocomplete="off">
		<aside class="sidebar rightbar white-bg border-box shadow--x">
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

  		var catalog = document.querySelector("main > .wrapper .catalog");
  		initCatalog(catalog);
	});
	</script>
</body>
</html>