<?php
	
	$rows = $mySQL->get("SELECT * FROM cb_brands ORDER BY idx LIMIT 30");
	$handle = "s:".time();

	$BrandID = file_get_contents('php://input');
?>
<div id="<?=$handle?>" class="mount" style="width:680px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.labels-box>div.box-body{
		font-size:0;
	}
	.labels-box>div.box-body>section{
		height:500px;
		max-height:80vh;
		vertical-align:top;
		display:inline-block;
	}
	.labels-box>div.box-body>section:nth-child(1){
		width:58px;
		padding:6px;
		font-size:15px;
		box-sizing:border-box;
	}
	.labels-box>div.box-body>section:nth-child(2){
		overflow:auto;
		width:calc(100% - 358px);
	}
	.labels-box>div.box-body>section:nth-child(3){
		width:300px;
		font-size:15px;
		padding:20px 50px;
		box-sizing:border-box;

		text-align: justify;
		text-align-last: center; 
	}
	section.indexes>label{
		width:20px;
		cursor:pointer;
		line-height:24px;
		text-align:center;
		display:inline-block;
	}
	section.indexes>label:hover,
	section.indexes>label>input:checked+span{
		color:#00ADF0;
	}
	.labels-box>div.box-body>section>img{
		color:#555;
		width:100%;
		height:128px;
		display:inline-block;
		cursor:pointer;
		position:relative;
		border-radius:5px;
		object-fit:contain;
		background-color:white;
	}
	.labels-box>div.box-body>section>img:hover{
		color:#00ADF0;
	}
	.labels-box>div.box-body>section>img::after{
		content:"\e94a";
		left:0;
		width:100%;
		height:100%;
		position:absolute;
		text-align:center;
		font:24px/100px tools;
	}
	.labels-box>div.box-body>section>input{
		padding:8px;
		border-width:0;
		border-radius:3px;
		vertical-align:middle;
		box-sizing:border-box;
		box-shadow:inset 0 0 5px 0 rgba(0,0,0, .5);
		background-image:linear-gradient(to top, #FFF, #EEE);
	}
	.labels-box>div.box-body>section>input[name='brand']{
		margin:8px 0;
		width:calc(100% - 40px);
	}
	.labels-box>div.box-body>section>input[name='idx']{
		width:30px;
		text-align:center;
	}
	.labels-box>div.box-body>section>label,
	.labels-box>div.box-body>section>output{
		color: white;
		display: inline-block;
	}
	.labels-box>div.box-body>section>output::before{
		color:#888;
		content:"ID: ";
	}
	</style>
	<form class="box labels-box white-bg" autocomplete="off">
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xe9d2;<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg" data-translate="textContent">labels</div>
		<div class="box-body">
			<section class="black-bg indexes">
				<?foreach($mySQL->getGroup("SELECT idx FROM cb_brands GROUP BY idx ORDER BY idx")['idx'] as $idx):?>
				<label><input type="radio" name="index" value="<?=$idx?>" hidden><span><?=$idx?></span></label>
				<?endforeach?>
			</section>
			<section>
				<table width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
					<thead>
						<tr class="h-bar-bg">
							<th class="l" width="36">
								<label class="tool" title="add label" data-translate="title"><input name="addLabel" type="checkbox" hidden>&#xe146;</label>
							</th>
							<th data-translate="textContent">named</th>
							<th width="30"></th>
						</tr>
					</thead>
					<tbody align="center">
						<?foreach($rows as $row):?>
						<tr data-id="<?=$row['BrandID']?>">
							<td><?=$row['BrandID']?></td>
							<td><?=$row['brand']?></td>
							<th class="tool drop-row" title="delete label" data-translate="title">✕</th>
						</tr>
						<?endforeach?>
					</tbody>
				</table>
			</section>
			<section class="body-bg light-txt">
			<?if(!empty($BrandID)):
				$brand = $mySQL->getRow("SELECT * FROM gb_brands WHERE BrandID = {int} LIMIT 1", $BrandID)?>
				
				<output name="BrandID" class="dark-txt"><?=$brand['BrandID']?></output>
				<label><input type="checkbox" name="favorite"> <span>Favorite</span></label>	
				
				<input name="brand" value="<?=$brand['brand']?>" placeholder="Named">
				<input name="idx" value="<?=$brand['idx']?>">
				<img src="<?=$brand['logo']?>" alt="" vspace="8">
			<?endif?>
			</section>
		</div>
		<div class="box-footer light-btn-bg" align="right">
			<button type="submit" class="light-btn-bg" data-translate="textContent" name="send" hidden disabled>apply</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<template>
			<td></td>
			<td></td>
			<th class="tool drop-row" title="delete service" data-translate="title">✕</th>
		</template>
		<script>
		(function(form){
			var timeout;
			form.onreset=function(event){form.drop()}
			form.addLabel.onchange=function(){
				XHR.push({
					addressee:"/labels/actions/add-label",
					onsuccess:function(response){
						if(parseInt(response)){
							let row = doc.create("tr",{"data-id":response},form.querySelector("template").cloneNode(true).content);
							form.querySelector(".box-body>section:nth-child(2)>table>tbody").appendChild(row);
							row.querySelector("td:nth-child(1)").textContent=response;
							form.init();
							form.align();
						}
					}
				});
			}
			form.querySelector("div.box-body>section:nth-child(1)").onchange=function(event){
				XHR.push({
					addressee:"/labels/actions/reload/"+event.target.value,
					onsuccess:function(response){
						form.querySelector("div.box-body>section:nth-child(2)>table>tbody").innerHTML = response;
						form.init();
					}
				});
			}
			form.init=function(){
				var tbody = form.querySelector(".box-body>section:nth-child(2)>table>tbody");
				tbody.querySelectorAll("tr").forEach(function(row){
					row.onclick=function(){
						XHR.push({
							addressee:"/labels/actions/get-label/"+row.dataset.id,
							onsuccess:function(response){
								let label = form.querySelector(".box-body>section:nth-child(3)");
									label.innerHTML = response;
								form.send.disabled = false;
								label.querySelector("img").onclick=function(event){
									new Box(null, "boxfather/imagebox",function(frm){
										event.target.src = frm.querySelector("iframe").contentWindow.getSelectedURLs();
										XHR.push({
											addressee:"/labels/actions/change/"+form.BrandID.value,
											body:'{"logo":"'+event.target.src+'"}'
										});
									});
								}
								form.favorite.oninput = function(){
									XHR.push({
										addressee:"/labels/actions/change/"+form.BrandID.value,
										body:'{"favorite": '+(form.favorite.checked ? 1 : 0)+'}'
									});
								}
								form.idx.oninput = 
								form.brand.oninput = function(event){
									clearTimeout(timeout);
									timeout = setTimeout(function(){
										XHR.push({
											addressee:"/labels/actions/change/"+form.BrandID.value,
											body:'{"'+event.target.name+'":"'+event.target.value+'"}'
										});
									},1000);
								}
							}
						});
					}
				});
				tbody.querySelectorAll("tr>th.drop-row").forEach(function(cell){
					cell.onclick=function(){
						let row = cell.parentNode;
						XHR.push({
							addressee:"/labels/actions/delete/"+row.dataset.id,
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