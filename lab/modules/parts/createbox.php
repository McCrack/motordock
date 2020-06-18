<?php

// ARG_2 - CAT_ID
// ARG_3 - LINE_ID
// POST DATA - LABEL_ID

$Label_ID = file_get_contents('php://input');

$handle = "b:".time()
?>
<div id="<?=$handle?>" class="mount modal">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.create-box>div.box-caption{
		font-size:18px;
		line-height:26px;
	}
	.create-box{
		width:400px;
		max-width:98%;
	}
	.create-box>div.box-body>fieldset{
		font-size:0;
		border-width:0;
		white-space:nowrap;
		margin:10px 10px 0 10px;
	}
	.create-box>div.box-body>fieldset>legend{
		font-size:15px;
	}
	.create-box>div.box-body>fieldset>textarea{
		height:60px;
		border-width:0;
		resize:vertical;
		width:99%;
		padding:6px;
		box-sizing:border-box;
		font:bold 15px calibri, helvetica, arial;
	}
	.create-box>div.box-body>fieldset>input,
	.create-box>div.box-body>fieldset>div.select{
		padding:6px;
		border:1px solid #AAA;
		box-sizing:border-box;
	}
	.create-box>div.box-body>fieldset>input[name='reference']{
		width:99%;
	}
	.create-box>div.box-body>fieldset:nth-child(3)>div.select{
		width:48%;
		margin:0 .8%;
	}
	.create-box>div.box-body>fieldset>div.select>select{
		width:100%;
	}
	.create-box>div.box-body>fieldset>div.select>select[name='language']{
		text-transform:uppercase;
	}
	</style>
	<form class="box create-box light-btn-bg">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption dark-btn-bg white-txt">
			<span data-translate="textContent">create item</span>
			<script>
			(function(bar){
				bar.onmousedown=function(event){
					event.preventDefault();
					var mount = bar.parentNode,
						y = event.clientY - mount.offsetTop,
						x = event.clientX - mount.offsetLeft;
					document.onmousemove=function(event){
						let top = event.clientY - y;
						let left = event.clientX - x;
						mount.style.top = (top > 0) ? top+"px" : "0";
						mount.style.left = (left > 0) ? left+"px" : "0";
					}
					document.onmouseup=function(){document.onmousemove = null;}
				}
			})(document.currentScript.parentNode)
			</script>
		</div>
		<div class="h-bar"></div>
		<div class="box-body">
			<fieldset class="right"><legend data-translate="textContent">articul</legend>
				<input name="articul" value="<?=$mySQL->getRow("SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name LIKE 'gb_parts'")['AUTO_INCREMENT']?>" size="12" required>
			</fieldset>
			<fieldset><legend data-translate="textContent">category</legend>
				<div class="select">
					<select name="category">
						<?php
						$parts = $mySQL->get("SELECT * FROM gb_sitemap WHERE parent LIKE 'showcase' ORDER BY SortID ASC");
						$categories = $mySQL->getTree("category","PageID","SELECT * FROM gb_categories");
						foreach($parts as $itm):?>
						<optgroup label="<?=$itm['header']?>">
							<?foreach($categories[$itm['PageID']] as $cat):?>
							<option <?if($cat['CatID']==ARG_2):?>selected<?endif?> value="<?=$cat['CatID']?>"><?=$cat['alias']?></option>
							<?endforeach?>
						</optgroup>
						<?endforeach?>
					</select>
				</div>
			</fieldset>
			<fieldset><legend data-translate="textContent">lineup</legend>
				<div class="select">
					<select name="label">
						<?if(!$label):?><option selected disabled>Choose a Car Mark</option><?endif?>
						<?foreach($mySQL->getTree("link","idx","SELECT * FROM gb_labels") as $idx=>$marks):?>
						<optgroup label="<?=$idx?>">
							<?foreach($marks as $label):?>
							<option <?if($label['LabelID']==$Label_ID):?>selected<?endif?> value="<?=$label['LabelID']?>"><?=$label['label']?></option>
							<?endforeach?>
						</optgroup>
						<?endforeach?>
					</select>
				</div>
				<div class="select">
					<select name="lineup">
						<?if(!ARG_3):?><option selected disabled value="0">Choose a Lineup</option><?endif?>
						<?if($Label_ID) foreach($mySQL->get("SELECT LineID,Model FROM gb_lineups WHERE LabelID={int}",$Label_ID) as $lineup):?>
						<option <?if($lineup['LineID']==ARG_3):?>selected<?endif?> value="<?=$lineup['LineID']?>"><?=$lineup['Model']?></option>
						<?endforeach?>
					</select>
				</div>
			</fieldset>
			<fieldset><legend>Referense</legend>
				<input name="reference">
			</fieldset>
			<fieldset><legend data-translate="textContent">named</legend>
				<textarea name="named" placeholder="..."></textarea>
			</fieldset>
		</div>
		<div class="box-footer" align="center">
			<button type="submit" name="create" class="light-btn-bg" data-translate="textContent">create</button>
			<button type="reset" class="dark-btn-bg" data-translate="textContent">cancel</button>
		</div>
		<script>
		(function(form){
			form.onreset=function(){ form.drop(); }
			form.onsubmit=function(event){
				event.preventDefault();
				if(form.lineup.value>0) XHR.push({
					addressee:"/actions/parts/ad_item",
					body:JSON.encode({
						LineID:form.lineup.value,
						CatID:form.category.value,
						Reference:form.reference.value.trim(),
						RefID:parseInt(form.articul.value),
						named:form.named.value.trim().replace(/"/g,"″")
					}),
					onsuccess:function(response){
						setTimeout(function(){location.pathname = "parts/"+response}, 100);
						form.drop();
					}
				}); else alert("Please choose a Lineup");
			}
			form.label.onchange=function(){
				XHR.push({
					addressee:"/actions/parts/gt_lineups/"+form.label.value,
					onsuccess:function(response){
						form.lineup.innerHTML = response;
					}
				});
			}

			location.hash = "<?=$handle?>";
			translate.fragment(form);
			form.style.top = "calc(50% - "+(form.offsetHeight/2)+"px)";
		})(document.currentScript.parentNode);
		</script>
	</form>
</div>
