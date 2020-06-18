<?php
	if(file_exists("patterns/json/checkboard.json")){
		$checkboard = JSON::load("patterns/json/checkboard.json");
	}else{
		$checkboard = [
			"Деплоймент"=>[
				[
					"caption"=>"Регистрация домена",
					"status"=>"checked"
				],
				[
					"caption"=>"Регистрация поддоменов",
					"status"=>"checked"
				],
				[
					"caption"=>"Развертывание системы",
					"status"=>"checked"
				]
			]
		];
		JSON::save("patterns/json/checkboard.json",JSON::encode($board));
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/checkboard/index.css">
		<script src="/xhr/wordlist?d[0]=base&d[1]=shunter" async charset="utf-8" onload="translate.fragment()"></script>
		<style>
		main{
			padding:1vw;
			overflow:auto !important;
		}
		</style>
	</head>
	<body>
			<main class="light-txt">
				<form autocomplete="off">
					<?foreach($checkboard as $caption=>$board):?>
					<fieldset>
						<legend class="h-bar t red-txt">
							<span class="white-txt"><?=$caption?></span>
							<label class="tool" title="add task" data-translate="title" onclick="addItemToBoard('<?=$caption?>', this.parent(2))">&#xe146;</label>
						</legend>
						<?foreach($board as $i=>$itm):?>
						<label class="<?=$itm['status']?>" title="<?=$itm['caption']?>">
							<input type="checkbox" value="<?=$i?>" name="<?=$caption?>" hidden>
							<?if(!empty($itm['log'])):?>
							<i>ℹ</i>
							<div class="log">
								<div class="red-txt"><?=$itm['caption']?></div>
								<?foreach($itm['log'] as $logitem):?>
								<div class="gold-txt"><?=$logitem?></div>
								<?endforeach?>
							</div>
							<?endif?>
						</label>
						<?endforeach?>
					</fieldset><hr>
					<?endforeach?>
					<script>
					(function(form){
						var timeout;
						form.onchange=function(event){
							switch(event.target.parentNode.className){
								case "enabled": var status = "checked"; break;
								case "disabled": var status = "enabled"; break;
								case "checked": var status = "disabled"; break;
								default:break;
							}
							event.target.parentNode.className = status;
							clearTimeout(timeout);
							timeout = setTimeout(function(){
								XHR.push({
									addressee:"/checkboard/actions/checked",
									body:JSON.encode({
										board:event.target.name,
										item:event.target.value,
										status:status
									})
								});
							},300);
						}
					})(document.currentScript.parentNode)
					function addItemToBoard(caption,board){
						promptBox("task caption",function(form){
							XHR.push({
								addressee:"/checkboard/actions/add-item",
								body:JSON.encode({
									board:caption,
									task:form.field.value.trim().replace(/"/g,"”"),
								}),
								onsuccess:function(response){
									board.innerHTML += response;
								}
							});
						},["active-bg"]);
					}
					</script>
				</form>
			</main>
	</body>
</html>