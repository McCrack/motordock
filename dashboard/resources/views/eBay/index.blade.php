@extends('index')

@section('styles')
<style>
input[name='period']:not(:checked)+span{
	opacity: .4;
	pointer-events: none;
}
/** Spinner **/
.spinner > .slide{
    padding-bottom: 74%;
}
.spinner-btn{
    transition: font-size .3s;
    text-shadow: 0 0 4px #00000050;
}
.spinner-btn:hover{
    font-size: 32px;
}
.carousel-items > label{
    max-height: 100px;
}

/** Keywords **/

.frm{
	width: 5px;
	transition: width .3s;
}
.keyword{
	margin: 3px 1px;
	font-size: 11px;
	border-width: 0;
	border-radius: 3px;
	position: relative;
	display: inline-block;
	vertical-align: middle;
	padding: 5px 6px 4px 10px;
	box-shadow: 4px 4px 4px -4px #00000050;
}
.keyword > div{
	min-height: 14px;
}
.keyword > hr{
	top: 0;
	left: 0;
	cursor: grab;
	width: 5px;
	height: 100%;
	position:absolute;
	background-color: #FFFFFF60;
}
.keyword > span.drop{
	top: -5px;
	right: -5px;
	width: 12px;
	height: 12px;
	color: black;
	cursor: pointer;
	line-height: 12px;
	text-align: center;
	position:absolute;
	border-radius: 50%;
	background-color: white;
	box-shadow: 2px 2px 3px -2px #00000050;
}
.keyword > span.drop::before{
	content: "╳";
}
</style>
@endsection

@section('scripts')
<!-- SCRIPTS -->
@endsection


@section('sidebar')
<input id="filters-tab" type="radio" name="sidebar-tabs" hidden checked autocomplete="on">
<label for="filters-tab" class="tab-btn dark-color">Filters</label>
<div class="tab">
	<fieldset class="card rounded-3 my-5 mx-10">
		<div class="card-title font-size-14 text-bold my-10">Sellers</div>
		<div class="font-size-13">
			@foreach($sellers as $seller)
			<label class="block my-4">
				<input form="search" type="checkbox" name="seller" value="{{ $seller->SellerID }}" data-market="{{ array_search($seller->market, $cng->markets) }}" autocomplete="off">
				{{ $seller->StoreName }}
				<small class="float-right dark-txt text-regular">{{ $seller->market }}</small>
			</label>
			@endforeach
		</div>
		<script>
		(function(container){
			container.onchange = function(event){
				event.target.form.market.value = event.target.dataset.market;
				container.querySelectorAll("input:checked").forEach(function(inp){
					if(inp.dataset.market != event.target.dataset.market){
						inp.checked = false;
					}
				});
			}
		})(document.currentScript.parentNode)
		</script>
	</fieldset>
	<fieldset class="card rounded-3 my-5 mx-10">
		@include('eBay.components.categories')
	</fieldset>
</div>
@endsection


@section('layout')
<div class="container max-w-900">
	<a href="https://ebay.com" target="_blank">
		<img src="/images/logotypes/ebay.png" width="100" class="float-left greyscale hover-colorize">
	</a>
	<div class="tabs py-1 text-right">
		<input id="inStore-tab" type="radio" name="submodules-tabs" autocomplete="off" data-view="inStore" data-loaded="false" hidden>
		<label for="inStore-tab" class="tab-btn white-color">In Store</label>
		<div class="tab text-left">
			
		</div>

		<input id="keywords-tab" type="radio" name="submodules-tabs" autocomplete="off" data-view="research" data-loaded="true" hidden checked>
		<label for="keywords-tab" class="tab-btn white-color">Research</label>
		<div class="tab text-left">
			@include('eBay.components.research')
		</div>

		<input id="categories-tab" type="radio" name="submodules-tabs" autocomplete="off" data-view="category" data-loaded="false" hidden>
		<label for="categories-tab" class="tab-btn white-color" title="options">Category</label>
		<div class="tab text-left">
			
		</div>
		{{--
		<input id="dictionary-tab" type="radio" name="submodules-tabs" hidden autocomplete="on">
		<label for="dictionary-tab" class="tab-btn white-color">Dictionary</label>
		<div class="tab text-left">
			@include('eBay.components.dictionary')
		</div>
		--}}
		<input id="sellers-tab" type="radio" name="submodules-tabs" autocomplete="off" data-view="sellers" data-loaded="false" hidden>
		<label for="sellers-tab" class="tab-btn white-color">Sellers</label>
		<div class="tab text-left">
			
		</div>
	
		<input id="options-tab" type="radio" name="submodules-tabs" autocomplete="off" data-view="options" data-loaded="false" hidden>
		<label for="options-tab" class="tab-btn dark-color" title="options"><span class="icon-cog"></span></label>
		<div class="tab text-left">

		</div>
		<br clear="left">
	</div>
	<script>
	(function(container){
		container.querySelectorAll(".tabs > input[name='submodules-tabs']").forEach(function(inp){
			inp.onchange = function(event){
				if(event.target.dataset.loaded == "false"){
					XHR.push({
						addressee: "/eBay/ld_tab/"+event.target.dataset.view+"/{{ $category->id }}",
						onsuccess: function(response){
							inp.dataset.loaded = "true";
							var tab = container.querySelector(".tabs > input:checked + label + div");
							tab.innerHTML = response;

							tab.querySelectorAll("script").forEach(function(sct){
							var script = document.createElement("script");
							if(sct.src){
								script.src = sct.src;
							}else script.innerHTML = sct.innerHTML;
								sct.parentNode.replaceChild(script, sct);
							});
						}
					});
				}
			}
		});
	})(document.currentScript.parentNode)
	</script>
</div>
@endsection