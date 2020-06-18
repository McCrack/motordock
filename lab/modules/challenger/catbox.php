<?php
	
	$parts = $mySQL->get("SELECT PageID,header FROM gb_sitemap WHERE parent LIKE 'showcase'");
	$handle = "s:".time();
?>
<div id="<?=$handle?>" class="mount" style="width:780px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.catbox>div.box-caption{
		font-size:16px;
	}
	.catbox>div.h-bar>div.select>select{
		font-size:14px;
	}
	</style>
	<form class="box catbox white-bg" autocomplete="off">
		<input id="addlineup" name="addlineup" type="checkbox" hidden>
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xe925;<?include_once("components/movebox.php")?></div>
		<div class="h-bar h-bar-bg">
			<div class="select">
				<select class="dark-txt">
					<?foreach($parts as $part):?>
					<option <?if($part['PageID']==36):?>selected<?endif?> value="<?=$part['PageID']?>"><?=$part['header']?></option>
					<?endforeach?>
				</select>
			</div>
		</div>
		<div class="box-body">
			<table width="100%" cellspacing="0" cellpadding="5" rules="cols" bordercolor="#CCC">
				<thead>
					<tr class="light-btn-bg">
						<th width="36">ID</th>
						<th>Category</th>
						<th>Alias</th>
						<th width="128"></th>
					</tr>
				</thead>
				<tbody>
					<?foreach($mySQL->get("SELECT * FROM gb_categories WHERE PageID=36") as $cat):?>
					<tr data-id="<?=$cat['CatID']?>">
						<td align="center"><?=$cat['CatID']?></td>
						<td><?=$cat['category']?></td>
						<td contenteditable="true"><?=$cat['alias']?></td>
						<td>
							<div class="select">
								<select name="part" class="black-txt" data-id="<?=$cat['CatID']?>">
									<?foreach($parts as $part):?>
									<option <?if($part['PageID']==$cat['PageID']):?>selected<?endif?> value="<?=$part['PageID']?>"><?=$part['header']?></option>
									<?endforeach?>
								</select>
							</div>
						</td>
					</tr>
					<?endforeach?>
				</tbody>
				<script>
				(function(body){
					body.oninput=function(event){
						if(event.target.nodeName=="TD"){
							clearTimeout(event.target.timeout);
							event.target.timeout = setTimeout(function(){
								XHR.push({
									addressee:"/actions/challenger/ch_alias/"+event.target.parentNode.dataset.id,
									body:event.target.textContent.trim()
								});
							},2000);
						}
					}
				})(document.currentScript.parentNode)	
				</script>
			</table>
		</div>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
			form.onchange=function(event){
				if(event.target.name){
					XHR.push({
						addressee:"/actions/challenger/ch_part/"+event.target.dataset.id+"/"+event.target.value,
					});
				}else XHR.push({
					addressee:"/actions/challenger/gt_part/"+event.target.value,
					onsuccess:function(response){
						form.querySelector(".box-body>table>tbody").innerHTML = response;
					}
				});
			}
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