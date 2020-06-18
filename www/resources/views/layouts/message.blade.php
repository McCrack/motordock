<div class="message white-bg border-tiny absolute">
	<img src="{{ $image }}" class="fit-cover w-100" height="120">
	<article class="px-3 py-1 my-20 text-regular">
		<p class="h3 mb-20 text-medium">{{ $header }}</p>
		{!! $message !!}
		<div class="text-right mt-10">
			<label for="message-toggler" class="btn btn-primary btn-lg">Ok</label>
		</div>
	</article>
</div>