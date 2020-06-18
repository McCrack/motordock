<form id="orderForm" class="pop-up-body dark-bg max-w-800" autocomplete="on">
	{{ csrf_field() }}
	<label for="order-toggler" class="cursor-pointer m-10 inline-block float-right light-txt">✕</label>
	<div class="tabs-md p-1">
		<input type="radio" hidden checked>
		<label class="tab-btn">@lang('dictionary.order-form')</label>
		<div class="tab white-bg">
			<div class="tab-body p-2">
				<div class="flex-md align-items-stretch">
					<div class="px-1 w-100">
						<p class="h4 mb-10">@lang('dictionary.contact-info')</p>
						<fieldset>
							<label for="order-name" class="dark-txt">@lang('dictionary.name') <sup class="red-txt">*</sup></label>
							<input id="order-name" type="text" name="Name" required>
						</fieldset>
						<fieldset>
							<label for="order-lastName" class="dark-txt">@lang('dictionary.last-name') <sup class="red-txt">*</sup></label>
							<input id="order-lastName" type="text" name="lastName" required>
						</fieldset>
						<fieldset>
							<label for="order-phone" class="dark-txt">@lang('dictionary.tel') <sup class="red-txt">*</sup></label>
							<input id="order-phone" type="tel" name="phone" required>
						</fieldset>
						<fieldset>
							<label for="order-email" class="dark-txt">Email <sup class="red-txt">*</sup></label>
							<input id="order-email" type="email" name="email" required>
						</fieldset>
					</div>
					<div class="px-1 w-100">
						<p class="h4 mb-10">@lang('dictionary.delivery')</p>
						<fieldset class="float-left">
							<label for="order-city" class="dark-txt">@lang('dictionary.city') <sup class="red-txt">*</sup></label>
							<input id="order-city" type="text" name="city" required>
						</fieldset>
						<fieldset>
							<label for="order-postcode" class="dark-txt">@lang('dictionary.postcode') <sup class="red-txt">*</sup></label>
							<input id="order-postcode" type="text" name="postcode" size="6" required>
						</fieldset>
						<fieldset>
							<label for="order-address" class="dark-txt">@lang('dictionary.address') <sup class="red-txt">*</sup></label>
							<input id="order-address" type="text" name="address" required>
						</fieldset>
						<fieldset>
							<label for="order-message" class="dark-txt">@lang('dictionary.order-comment')</label>
							<textarea id="order-message" name="message" rows="4" class="resize-vertical" placeholder="..."></textarea>
						</fieldset>
					</div>
				</div>
				<div class="accordion py-2 white-bg font-size-14">
        			<input id="privacy-policy-order" type="checkbox" name="atabs" hidden autocomplete="off">
        			<label for="privacy-policy-order" class="tab-btn">Datenschutzerklärung – Nutzung Ihrer Daten aus diesem Formular</label>
        			<div class="tab">
            			<div class="tab-body text-regular px-1">@policy</div>
        			</div>
        			<br class="clear-left">
    			</div>
				<div class="px-1 mt-20">
					<label class="cursor-pointer">
						<input name="signature" type="checkbox" autocomplete="off">
						<span class="terms-and-conditions-btn inline-block valign-middle">
						@lang("dictionary.privacy-policy-consent")
						</span>
					</label>
					<button name="orderbutton" disabled class="btn btn-success btn-lg float-right-md">@lang('dictionary.order')</button>
				</div>
			</div>
		</div>
		<br class="clear-left">
	</div>
	<script>
	(function(form){
		form.signature.onchange = function(){
			form.orderbutton.disabled = !form.signature.checked;
		}
		form.onsubmit = function(event){
			event.preventDefault();
			if(!form.signature.checked){
				return false;
			}
			PopupManager.reset();

			XHR.push({
				addressee: "/ajax/order",
				body: JSON.encode({
					name: form.Name.value,
					lastName: form.lastName.value,
					phone: form.phone.value,
					email: form.email.value,
					city: form.city.value,
					postcode: form.postcode.value,
					address: form.address.value,
					message: form.message.value,
					signature: (form.signature.checked ? 1 : 0),
					cart: CART,
					_token: form._token.value,
				}),
				onsuccess: function(response){
                	setTimeout(function(){
                		PopupManager.messageControl.checked = true;
                		document.querySelector("#message-toggler+div").innerHTML = response;

                		gtag_report_conversion();
                	}, 500);

					CART = {};
					window.localStorage.cart = '{}';
					document.querySelectorAll(".cart-btn").forEach(function(btn){
						btn.dataset.amount = 0;
					});
				}
			});
		}
	})(document.currentScript.parentNode)
	</script>
</form>