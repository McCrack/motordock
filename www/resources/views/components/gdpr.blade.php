<form class="alert px-4 py-1 white-bg flex justify-center align-items-center shadow--y sticky z-index-3">
	<div class="pr-3 text-regular">
		<p class="text-bold my-5">Wir verwenden Cookies</p>
		<p>
		Wir verwenden Google Analytics-Tools, die Cookies auf Ihrem Computer speichern können.
		<br>
		Lesen Sie mehr über <a href="/datenschutzerklaerung" target="_blank" class="blue-txt nowrap">die Datenschutzbestimmungen.</a>
		</p>
	</div>
	<button class="btn btn-light btn-md">Ok</button>
	<script>
	(function(form){
		form.onsubmit = function(event){
			event.preventDefault();
			window.localStorage.gdpr = 1;
			document.body.removeChild(form);
		}
		var GDPR = window.localStorage.gdpr || 0;
		if(GDPR){
			form.parentNode.removeChild(form);
		}else setTimeout(function(){
			form.classList.toggle('show', true);
		}, 3000);
	})(document.currentScript.parentNode)
	</script>
</form>