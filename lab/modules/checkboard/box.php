<?$h = "n".time()?>
<div id="<?=$h?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	form.checkboard-box{
		outline:1px solid #CCC;
	}
	form.checkboard-box>div.box-body{
		height:72vh;
		resize:both;
		font-size:0;
	}
	@media (max-width:600px){
		form.checkboard-box>div.box-body{
			width:98vw;
		}
	}
	@media (min-width:501px){
		form.checkboard-box>div.box-body{
			width:80vw;
		}
	}
	</style>
	<form class="box checkboard-box white-bg">
		<button type="reset" class="close-btn light-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">&#xe5d1;<?include_once("components/movebox.php")?></div>
		<div class="h-bar dark-btn-bg" data-translate="textContent">checkboard</div>
		<div class="box-body">
			<iframe width="100%" height="100%" frameborder="no"></iframe>
			<script>
				reauth();
				document.currentScript.previousElementSibling.contentWindow.location.href="/checkboard/embed";
			</script>
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
		})(document.currentScript.parentNode)
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$h?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>