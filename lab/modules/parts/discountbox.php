<?php
	
	$rows = $mySQL->get("SELECT * FROM gb_discounts ORDER BY DiscountID DESC");
	$handle = "s:".time();

	$DiscountID = defined("ARG_2") ? ARG_2 : 0;
?>
<div id="<?=$handle?>" class="mount" style="width:740px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.discount-box>div.box-caption{
		font-size:18px;
	}
	.discount-box>div.box-body{
		font-size:0;
	}
	.discount-box>div.box-body>section{
		vertical-align:top;
		display:inline-block;
	}
	.discount-box>div.box-body>section:nth-child(1){
		width:240px;
		overflow:auto;
	}
	.discount-box>div.box-body>section:nth-child(2){
		width:calc(100% - 240px);
		padding:10px;
		font-size:15px;
		text-align:right;
		box-sizing:border-box;
	}
	.discount-box>div.box-body>section input,
	.discount-box>div.box-body>section>div>output,
	.discount-box>div.box-body>section>textarea{
		margin:4px .5%;
		padding:8px 6px;
		border-radius:3px;
		vertical-align:middle;
	}
	.discount-box>div.box-body>section>input[type='date']{
		width:124px;
	}
	.discount-box>div.box-body>section>input[name='caption']{
		width:99%;
	}
	.discount-box>div.box-body>section>textarea{
		width:99%;
		height:200px;
		resize:vertical;
	}
	.discount-box>div.box-body>section>div>output{
		color:#777;
		font-size:20px;
	}
	.discount-box>div.box-body>section>div>output::after{
		color:#00ADF0;
		content:attr(value);
	}
	.discount-box>div.box-body>section>div>input[name='saved']+output::before{
		color:#777;
		content:"\f0c7";
		font-family:tools;
		margin-right:5px;
		vertical-align:top;
	}
	.discount-box>div.box-body>section>div>input[name='saved']:checked+output::before{
		color:#D44;
	}
	</style>
	<form class="box discount-box white-bg" autocomplete="off">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption logo-bg">&#xf069;<?include_once("components/movebox.php")?></div>
		<div class="h-bar dark-btn-bg" data-translate="textContent">discount</div>
		<div class="box-body">
			<section>
				<table width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
					<thead>
						<tr class="h-bar-bg">
							<th class="l" width="36">
								<label class="tool" title="add discount" data-translate="title"><input name="addbutton" type="checkbox" hidden>&#xe146;</label>
							</th>
							<th data-translate="textContent">named</th>
							<th width="30"></th>
						</tr>
					</thead>
					<tbody align="center">
						<?foreach($rows as $row): if($row['DiscountID']==$DiscountID) $discount = $row?>
						<tr data-id="<?=$row['DiscountID']?>">
							<td><?=$row['DiscountID']?></td>
							<td><?=$row['caption']?></td>
							<th class="tool drop-row" title="delete discount" data-translate="title">✕</th>
						</tr>
						<?endforeach?>
					</tbody>
				</table>
			</section>
			<section class="body-bg light-txt">
				<div align="left">
					<input type="checkbox" name="saved" hidden>
					<output value="<?=$DiscountID?>">Discount ID: </output>
					<input name="DiscountID" value="<?=$DiscountID?>" type="hidden">
				</div>
				<div class="left" style="white-space:nowrap;">
					<input name="sticker" value="<?=$discount['sticker']?>" class="text-field" placeholder="sticker" data-translate="placeholder" size="10">
					<input name="discount" value="<?=$discount['discount']?>" class="text-field" placeholder="value" data-translate="placeholder" size="8">
				</div>
				<span class="tool">&#xe900;</span>
				<input required name="begining" type="date" value="<?=date("Y-m-d", $discount['begining'])?>" class="text-field" size="9">
				<input required name="ended" type="date" value="<?=date("Y-m-d", $discount['ended'])?>" class="text-field" size="9"> 

				<input name="caption" value="<?=$discount['caption']?>" class="text-field" placeholder="named" data-translate="placeholder">
				<textarea name="essence" class="text-field" placeholder="description" data-translate="placeholder"><?=$discount['essence']?></textarea>
			</section>
		</div>
		<div class="box-footer light-btn-bg" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent" name="send">apply</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<template>
			<td></td>
			<td></td>
			<th class="tool drop-row" title="delete discount" data-translate="title">✕</th>
		</template>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
			form.addbutton.onchange=function(){
				XHR.push({
					addressee:"/actions/showcase/ad_discount",
					onsuccess:function(response){
						if(parseInt(response)){
							let row = doc.create("tr",{"data-id":response},form.querySelector("template").cloneNode(true).content);
							form.querySelector(".box-body>section:nth-child(1)>table>tbody").appendChild(row);
							row.querySelector("td:nth-child(1)").textContent=response;
							form.init();
							form.align();
						}
					}
				});
			}
			form.oninput=function(event){
				form.saved.checked=true;
				clearTimeout(event.target.timeout);
				event.target.timeout = setTimeout(function(){
					let val;
					if(event.target.type=="date"){
						var offset = new Date().getTimezoneOffset()*60000;
						val = utf8_to_b64((event.target.valueAsNumber+offset)/1000);
					}else val = utf8_to_b64(event.target.value);
					XHR.push({
						addressee:"/actions/showcase/ch_discount/"+form.DiscountID.value+"/"+event.target.name,
						body:val,
						onsuccess:function(response){
							if(parseInt(response)) form.saved.checked=false;
						}
					});
				},2500);
			}
			form.init=function(){
				var tbody = form.querySelector(".box-body>section:nth-child(1)>table>tbody");
				tbody.querySelectorAll("tr").forEach(function(row){
					row.onclick=function(){
						XHR.push({
							addressee:"/actions/showcase/gt_discount/"+row.dataset.id,
							onsuccess:function(response){
								form.querySelector(".box-body>section:nth-child(2)").innerHTML = response;
								translate.fragment(form);
							}
						});
					}
				});
				tbody.querySelectorAll("tr>th.drop-row").forEach(function(cell){
					cell.onclick=function(){
						let row = cell.parentNode;
						XHR.push({
							addressee:"/actions/showcase/dl_discount/"+row.dataset.id,
							onsuccess:function(response){
								if(parseInt(response)) tbody.removeChild(row);
							}
						});
					}
				});
			}
			form.init();
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