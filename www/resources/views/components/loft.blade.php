<div class="topbar flex align-items-center justify-between sticky top z-index-3 border-box shadow-y white-bg">
	<a href="/" class="text-bold font-large px-2 black-txt">
		<img
			src="/img/logo/black.png"
			width="128" 
			alt="Motordock Logo"
			title="Zur Homepage">
	</a>
	<nav class="navbar white-bg px-1 border-left-tiny flex-grow display-none-sm">
		@foreach($map['static'] as $itm)
			@if($itm['published']=="Published")		
			<a href="/{{ $itm['slug'] }}" class="hover-blue-txt">{{ $itm['header'] }}</a>
			@endif
		@endforeach
	</nav>
	<div class="toolbar">
		<label for="rightbar-toggler" class="tool cart-btn" data-amount="0">
			<!--
			<img
				src="/img/corb.png"
				alt="@lang('cart')"
				title="@lang('open-cart')">
			
			-->
			<svg class="w-52" viewBox="0 -40 580 580"><use xlink:href="/img/symbols.svg#cart"></svg>
			<script>
			var CART = JSON.parse( window.localStorage.cart || '{}' );
			(function(btn){
				btn.dataset.amount = Object.keys(CART).length;
			})(document.currentScript.parentNode)
			</script>
		</label>
		<label for="leftbar-toggler" class="tool display-none-md">
			<span class="hamburger">
				<span></span>
				<span></span>
			</span>
		</label>
	</div>
</div>