@extends('index')

@section('styles')
<style>

</style>
@endsection

@section('scripts')
<!-- SCRIPTS -->
@endsection


@section('sidebar')
<input id="staff-tab" type="radio" name="sidebar-tabs" hidden checked autocomplete="on">
<label for="staff-tab" class="tab-btn dark-color">Team</label>
<div class="tab">
	<fieldset class="card rounded-3 my-5 mx-10 px-1 py-2">
		@foreach($team as $key => $group)
			<fieldset class="border-none">
				<legend class="font-size-18 text-bold capitalize">{{ $key }}</legend>
				<div class="pl-1">
				@foreach($group as $usr)
					@if(isset($user) && ($user->id == $usr->id))
					<a class="card-title active-txt font-size-14 text-bold mb-5" href="/Users/{{ $usr->id }}">{{ $usr->name }}</a>
					@else
					<a class="card-title black-txt font-size-14 text-medium mb-5" href="/Users/{{ $usr->id }}">{{ $usr->name }}</a>
					@endif
				@endforeach
				</div>
			</fieldset>
		@endforeach
	</fieldset>
</div>
@endsection


@section('layout')
<form class="card p-4 container max-w-800" autocomplete="off">
	<div class="font-size-20">
		User ID:
		<output name="user_id" class="text-bold">{{ $user->id ?? null }}</output>
		@isset($user)
		<a class="red-txt hover-active-txt" href="/Users">×</a>
		@endisset
	</div>
	<div class="font-size-13 my-20 flex justify-between align-items-center">
		<div>
			<div class="my-5 blue-txt">Created at:</div>
			{{ $user->created_at ?? '0000-00-00 00:00' }}
		</div>
		<div class="text-right">
			<div class="my-5 blue-txt">Updated at: </div>
			{{ $user->updated_at ?? '0000-00-00 00:00' }}
		</div>
	</div>
	<div class="my-10 font-size-14 flex justify-between align-items-center">
		<fieldset class="border-none mx-0 px-0">
			<legend>Name</legend>
			<input type="text" name="name" value="{{ $user->name ?? null }}" class="field" required>
		</fieldset>
		<fieldset class="border-none mx-0 px-0">
			<legend>Email</legend>
			<input type="email" name="email" value="{{ $user->email ?? null }}" class="field" required>
		</fieldset>	

		<fieldset class="border-none mx-0 px-0">
			<legend>Password</legend>
			<input type="text" name="password" class="field" placeholder="●●●●●" @empty($user) required @endempty>
		</fieldset>
		<fieldset class="border-none mx-0 px-0">
			<legend>Access Group</legend>
			<select name="group" class="field field-size-120">
			@foreach([
				'guest',
				'admin',
				'developer',
				'manager',
				'partner'
			] as $group)
				@if($group == ($user->group ?? null))
				<option value="{{ $group }}" selected>{{ $group }}</option>
				@else
				<option value="{{ $group }}">{{ $group }}</option>
				@endif
			@endforeach
			</select>
		</fieldset>
	</div>
	<fieldset class="font-size-14 text-bold border-none m-0 p-0">
		<legend class="font-size-18">
			<span class="red-txt">e</span><span class="blue-txt">B</span><span class="yellow-txt">a</span><span class="green-txt">y</span>
			<span class="font-size-14">Auth key</span>
		</legend>
		<textarea name="token" class="border-tiny w-100 h-180 p-1 border-box resize-none font-size-12 dark-bg orange-txt">{!! $user->token ?? null !!}</textarea>
	</fieldset>

	<div class="mt-30">
		@if(empty($user))
		<button name="save" type="submit" disabled class="btn btn-md btn-primary">Create</button>
		@else
		<button name="save" type="submit" disabled class="btn btn-md btn-primary">Save</button>
		<button type="reset" class="btn btn-md btn-danger">Delete</button>
		@endif
	</div>
	<script>
	(function(form){
		form.oninput = 
		form.onchange = function(){
			form.save.disabled = false;
		}
		form.onreset = function(event){
			event.preventDefault();
			XHR.push({
				addressee: "/Users/delete/"+form.user_id.value,
				onsuccess: function(response){
					if(parseInt(response)){
						location.pathname = "/Users";
					}
				}
			});
		}
		form.onsubmit = function(event){
			event.preventDefault();
			var fields = {
				id: form.user_id.value,
				name: form.name.value,
				email: form.email.value,
				group: form.group.value,
				token: form.token.value
			};
			if(form.password.value){
				fields['password'] = form.password.value;
			}
			XHR.json({
				addressee: "/Users/save",
				body: fields,
				onsuccess: function(response){
					if(parseInt(response)){
						if(form.user_id.value){
							form.save.disabled = true;
						}else{
							location.pathname = "/Users/"+response;
						}
					}
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</form>
@endsection