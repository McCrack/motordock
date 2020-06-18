@foreach($makers as $make)
<label>
	<input name="makes"
		@if($make['slug'] == ($brand->slug ?? null)) checked @endif
		type="radio"
		value="{{ $make['slug'] }}"
		hidden>
	<span>{{ $make['brand'] }}</span>
</label>
@endforeach