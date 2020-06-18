@if(ALL_CATEGORIES)
<a class="block font-size-13 text-medium my-10 dark-txt hover-active-txt" href="/eBay">❮ Favorite Categories</a>
<div class="card-title font-size-20 black-txt text-bold mb-10">All Categories</div>
<div class="font-size-13 pl-1">
	@foreach($tree[1] as $part)
	<a
		href="/eBay/{{ $part['id'] }}"
		class="@if($part['id'] == $category->id) active-txt @else black-txt @endif font-size-15 my-10 hover-active-txt">{{ $part['name']['de'] }}</a>
	<div class="level mb-10 pl-1">
		@foreach($tree[$part['id']] as $level_1)
		<a 
			href="/eBay/{{ $level_1['id'] }}"
			class="@if($level_1['id'] == $category->id) active-txt @else dark-txt @endif block text-medium font-size-15 my-5 hover-active-txt">
			{{ $level_1['name']['de'] }}
		</a>
		@if(isset($tree[$level_1['id']]))
		<div class="level pl-1">
			@foreach($tree[$level_1['id']] as $level_2)
			<a
				href="/eBay/{{ $level_2['id'] }}"
				class="@if($level_2['id'] == $category->id) active-txt @else dark-txt @endif block text-regular font-size-14 my-5 hover-active-txt">
				{{ $level_2['name']['de'] }}
			</a>
			@if(isset($tree[$level_2['id']]))
			<div class="level pl-1">
				@foreach($tree[$level_2['id']] as $level_3)
				<a
					href="/eBay/{{ $level_3['id'] }}"
					class="@if($level_3['id'] == $category->id) active-txt @else dark-txt @endif block text-tiny font-size-13 my-5 hover-active-txt">
					{{ $level_3['name']['de'] }}
				</a>
				@endforeach
			</div>
			@endif
			@endforeach
		</div>
		@endif
		@endforeach
	</div>
	@endforeach
</div>
@else
<div class="card-title font-size-14 text-bold my-10">Favorite Categories</div>
<div class="font-size-13 pl-1">
@foreach($favorites as $cat)
	@if($cat->id == $category->id)
	<a class="active-txt block my-5" href="/eBay/{{ $cat->id }}">{{ $cat->name['de'] }}</a>
	@else
	<a class="block dark-txt my-5 hover-active-txt" href="/eBay/{{ $cat->id }}">{{ $cat->name['de'] }}</a>
	@endif
@endforeach
</div>
<div class="float-right m-5">
	<span class="dark-txt font-size-12 cursor-pointer hover-active-txt">All Categoties ❯</span>
	<script>
	(function(btn){
		btn.onclick = function(){
			XHR.push({
				method: "GET",
				addressee: "/eBay/allCategories/{{ $category->id }}",
				onsuccess: function(response){
					btn.parentNode.innerHTML = response;
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</div>
@endif