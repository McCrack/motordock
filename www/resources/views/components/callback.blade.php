<form class="pop-up-body dark-bg max-w-600" autocomplete="off">
	<label for="callback-toggler" class="cursor-pointer m-10 inline-block float-right light-txt">✕</label>
	<div class="tabs-md p-1">
		<input type="radio" hidden checked>
		<label class="tab-btn">@lang('dictionary.callback')</label>
		<div class="tab">
			<div class="tab-body p-2">
				<p class="h4">@lang('dictionary.contact-info')</p>
				<fieldset class="mt-10">
					<label for="callback-name" class="dark-txt">@lang('dictionary.name') <sup class="red-txt">*</sup></label>
					<input id="callback-name" type="text" name="Name" required autocomplete="on">
				</fieldset>
				<fieldset>
					<label for="callback-phone" class="dark-txt">@lang('dictionary.tel') <sup class="red-txt">*</sup></label>
					<input id="callback-phone" type="tel" name="phone" required autocomplete="on">
				</fieldset>
				<fieldset>
					<label for="callback-email" class="dark-txt">Email <sup class="red-txt">*</sup></label>
					<input id="callback-email" type="email" name="email" required autocomplete="on">
				</fieldset>
				<fieldset>
					<label for="callback-message" class="dark-txt">@lang('dictionary.comment')</label>
					<textarea id="callback-message" name="message" rows="4" class="resize-vertical" placeholder="..." autocomplete="on"></textarea>
				</fieldset>
				
				<div class="accordion py-1 white-bg font-size-14">
        			<input id="privacy-policy-callback" type="checkbox" name="atabs" hidden>
        			<label for="privacy-policy-callback" class="tab-btn">Datenschutzerklärung – Nutzung Ihrer Daten aus diesem Formular</label>
        			<div class="tab">
            			<div class="tab-body text-regular px-1">@policy</div>
        			</div>
					<br class="clear-left">
				</div>
				<div>
					<label class="cursor-pointer">
						<input name="signature" type="checkbox" autocomplete="off">
						<span class="terms-and-conditions-btn inline-block valign-middle">@lang("dictionary.privacy-policy-consent")</span>
					</label>
					<button type="submit" name="submitbutton" disabled class="btn btn-primary btn-lg float-right-md">@lang('dictionary.submit')</button>
				</div>
			</div>
		</div>
		<br class="clear-left">
	</div>
	<script>
	(function(form){
		form.signature.onchange = function(){
			form.submitbutton.disabled = !form.signature.checked;
		}
		form.onsubmit = function(event){
			event.preventDefault();
			PopupManager.reset();
			XHR.push({
				method: "POST",
				addressee: "/ajax/callback",
				body: JSON.encode({
					name: form.Name.value,
					phone: form.phone.value,
					email: form.email.value,
					message: form.message.value,
				}),
				onsuccess: function(response){
                	setTimeout(function(){
                		PopupManager.messageControl.checked = true;
                		document.querySelector("#message-toggler+div").innerHTML = response;
                	}, 500);
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</form>