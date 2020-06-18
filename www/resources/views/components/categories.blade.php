<div class="accordion white-bg">
	@foreach($catTree[1] as $part)

	<input id="atab-{{ $part['id'] }}" @if($part['name']==$breadcrumbs['itemListElement'][1]['name']) checked @endif type="radio" name="cattree" autocomplete="off" hidden>
	<label for="atab-{{ $part['id'] }}" class="tab-btn">{{ $part['name'] }}</label>

	<div class="tab white-bg">
		<div class="tab-body level mb-10">
		@foreach($catTree[$part['id']] as $level_1)

			<a
			href="/{{ $level_1['slug'] }}"
			class="mt-5 @if($level_1['id']==$category->id) selected @else black-txt @endif text-medium">
				{{ $level_1['name'] }}
			</a>
			@isset($breadcrumbs['itemListElement'][2])
			@if(isset($catTree[$level_1['id']]) && ($level_1['name']==$breadcrumbs['itemListElement'][2]['name']))
			<div class="level">
				@foreach($catTree[$level_1['id']] as $level_2)

				<a
				href="/{{ $level_2['slug'] }}"
				class="mt-5 @if($level_2['id']==$category->id) selected @endif text-regular">
					{{ $level_2['name'] }}
				</a>
				@if(isset($catTree[$level_2['id']]) && ($level_2['name']==$breadcrumbs['itemListElement'][2]['name']))
				<div class="level">
					@foreach($catTree[$level_2['id']] as $level_3)
					<a
					href="/{{ $level_3['slug'] }}"
					class="mt-5 @if($level_3['id']==$category->id) selected @endif text-tiny">
						{{ $level_3['name'] }}
					</a>
					@endforeach
				</div>
				@endif

				@endforeach
			</div>
			 @endif
			 @endisset
		@endforeach
		</div>
	</div>
	@endforeach
	<br class="clear-left">
</div>