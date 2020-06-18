<footer class="page-footer font-small footer-bg">
	<div class="orange-bg">
    	<div class="container">
      		<!-- Grid row-->
      		<div class="py-1 flex align-items-center">
        		<!-- Grid column -->
        		<div class="text-center text-md-left white-txt bold px-2">
                    @lang('dictionary.visit-our-page-on') <a class="white-txt underline" href="https://www.ebay-kleinanzeigen.de/pro/Motordock-Gmbh" target="_blank" rel="noopener">eBay kleinanzeigen</a>
        		</div>
        		<!-- Grid column -->
        		<!-- Grid column -->
        		<div class="text-center text-md-right">
          			
        		</div>
        		<!-- Grid column -->
			</div>
      	<!-- Grid row-->
    	</div>
    </div>
	<!-- Footer Links -->
	<div class="container px-2 tiles-2 tiles-3-md tiles-4-lg">
		<div class="py-3 light-txt inline-block w-100 box-sizing">
			<!-- Content -->
        	<p class="h4 uppercase white-txt">MOTORDOCK</p>
        	<hr class="orange-bg h-line w-25">
        	<svg viewBox="0 0 512 512" class="w-128">
        		<use fill="#DDDDDD" xlink:href="/img/symbols.svg#gears">
        	</svg>
		</div>

		<div class="py-3 inline-block w-100 box-sizing">
			<!-- Links -->
			<p class="h4 uppercase white-txt">@lang('dictionary.categories')</p>
			<hr class="orange-bg h-line w-25">
			@foreach($map['showcase'] as $itm)
			<a
				class="my-10 light-txt block hover-blue-txt"
				href="/{{ $itm['slug'] }}">{{ $itm['header'] }}</a>
			@endforeach
		</div>

		<div class="py-3 inline-block w-100 box-sizing">
			<!-- Links -->
			<p class="h4 uppercase white-txt">AGB</p>
			<hr class="orange-bg h-line w-25">
		@foreach($map['terms-and-conditions'] as $itm)
            @if($itm['published']=="Published")
			<a
				class="my-10 light-txt block hover-underline"
				href="/{{ $itm['slug'] }}">{{ $itm['header'] }}</a>
            @endif
		@endforeach

			<hr class="orange-bg h-line w-25">
		@foreach($map['static'] as $itm)
			@if($itm['published']=="Published")
			<a
				class="my-10 light-txt block hover-underline"
				href="/{{ $itm['slug'] }}">{{ $itm['header'] }}</a>
			@endif
		@endforeach
		</div>
		
		<div class="py-3 light-txt inline-block w-100 box-sizing">
			<!-- Links -->
			<p class="h4 uppercase white-txt">@lang('dictionary.contacts')</p>
			<hr class="orange-bg h-line w-25">
			<p class="my-5">
				<i class="fas fa-home mr-3"></i>
				Hannover, Wohlenbergstrasse 29
			</p>
			<p class="my-5">
				<i class="fas fa-envelope mr-3"></i>
				info@motordock.de
			</p>
			<p class="my-5">
				<i class="fas fa-phone mr-3"></i>
				+49 511-940-40914
			</p>
		</div>
	</div>
	<!-- Footer Links -->

	<!-- Copyright -->
	<div class="text-center py-1 main-bg-dark dark-txt">
		© {{date("Y")}} Copyright: MOTORDOCK
	</div>
	<!-- Copyright -->
</footer>