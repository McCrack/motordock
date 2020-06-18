<?$handle = "b".time();?>

<div id="<?=$handle?>" class="mount" style="width:540px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	
	</style>
	<form class="box discount-box white-bg" autocomplete="off">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption active-bg">&#xe90b;<?include_once("components/movebox.php")?></div>
		<div class="h-bar dark-btn-bg" data-translate="textContent">stock</div>
		<div class="box-body">
			<table rules="cols" width="100%" cellpadding="5" cellspacing="0" bordercolor="#CCC">
				<thead>
					<tr class="light-btn-bg">
						<th width="28"></th>
						<th data-translate="textContent">stock</th>
						<th width="120" data-translate="textContent">remainder</th>
						<th width="28"></th>
					</tr>
				</thead>
				<tbody>
				<?$names = $mySQL->getGroup("SELECT stock FROM gb_stock GROUP BY stock");
				if(empty($names)):?>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"></td>
						<td contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
				<?else: $items = $mySQL->getGroup("SELECT remainder FROM gb_stock WHERE ItemID={int}", ARG_2)['remainder'];
					foreach($names['stock'] as $key=>$stock):?>
					<tr>
						<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
						<td contenteditable="true"><?=$stock?></td>
						<td contenteditable="true"><?=(INT)$items[$key]?></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</tr>
					<?endforeach;
				endif?>
				</tbody>
			</table>
		</div>
		<div class="box-footer light-btn-bg" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent">save</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
		})(document.currentScript.parentNode);
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>