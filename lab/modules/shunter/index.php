<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	
	$cng = JSON::load("modules/shunter/config.init");

	define("MODE", defined("ARG_1") ? ARG_1 : $cng['tab']['value']);

	$hidden_tabs = preg_split("/\s*,+\s*/", $mySQL->settings['shunter']['hidden tabs'], -1, PREG_SPLIT_NO_EMPTY);

	$privileged = preg_split("/,\s*/", $cng['privileged groups']['value'], -1, PREG_SPLIT_NO_EMPTY);
	//$cng = new config("../".BASE_FOLDER."/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/shunter/index.css">
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=shunter" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" <?if(defined("ARG_2")):?>checked<?endif?> hidden>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar logo-bg t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label for="rightbar-shower"></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
					</div>
					<div class="toolbar">
						<label title="navigator" class="tool" data-translate="title" onclick="new Box(null, 'navigator/box')">&#xf07c;</label>
						<label title="mediaset" class="tool" data-translate="title" onclick="new Box(null, 'mediaset/box')">&#xe94b;</label>
					</div>
					<div class="toolbar">
						<label title="keywords" class="tool" data-translate="title" onclick="new Box(null, 'keywords/box')">&#xe9d3;</label>
						<?if($access['settings']):?>
						<label title="settings" class="tool" data-translate="title" onclick="new Box(null, 'settings/box')">&#xf013;</label>
						<?endif?>
						<?if($access['staff']):?>
						<label title="staff" class="tool" data-translate="title" onclick="new Box(null, 'staff/box')">&#xe972;</label>
						<?endif?>
					</div>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						/*
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							if(event.target.id!="left-default") STANDBY.leftbar = event.target.id;
						}});
    					if(STANDBY.leftbar) bar[STANDBY.leftbar].checked = true;
    					*/
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar light-txt">
				<div class="toolbar t">
					<label title="create task" data-translate="title" class="tool" onclick="new Box(null, 'shunter/createbox/<?=MODE?>');">&#xe402;</label>
				</div>
				<hr class="separator">
				<div class="toolbar t">
					<?if(!in_array("statuses", $hidden_tabs)):?>
					<a class="<?if(MODE=='statuses'):?>gold-txt<?else:?>light-txt<?endif?>" href="/shunter/statuses"><label title="statuses" data-translate="title" class="tool" onclick="">&#xe9b5;</label></a>
					<?endif; if(!in_array("performers", $hidden_tabs)):?>
					<a class="<?if(MODE=='performers'):?>gold-txt<?else:?>light-txt<?endif?>" href="/shunter/performers"><label title="performers" data-translate="title" class="tool">&#xe972;</label></a>
					<?endif; if(!in_array("types", $hidden_tabs)):?>
					<a class="<?if(MODE=='types'):?>gold-txt<?else:?>light-txt<?endif?>" href="/shunter/types"><label title="types" data-translate="title" class="tool" onclick="">&#xf069;</label></a>
					<?endif?>
				</div>
				<hr class="separator">
				<div class="toolbar t">
					<label title="filters" data-translate="title" class="tool" onclick="new Box(null, 'shunter/settingsbox');">&#xe992;</label>
					<label title="clear taskboard" data-translate="title" class="tool" onclick="new Box(null, 'shunter/clearbox');">&#xe9ac;</label>
				</div>
				<hr class="separator">
				<div class="toolbar t">
					<label title="checkboard" class="tool" data-translate="title" onclick="new Box(null, 'checkboard/box')">&#xe5d1;</label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
					<?if(defined("ARG_2")):?>
					<div class="toolbar t right">
						<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
					</div>
					<?endif?>
				</div>
			</header>
			<main>
				<?if(!in_array(MODE, $hidden_tabs)) include_once("modules/shunter/modes/".MODE.".php")?>
				<script>
				(function(board){
					board.querySelectorAll(".slot").forEach(function(slot){
						slot.onclick=function(){
							var path = location.pathname.split(/\//);
								path[1] = "shunter";
								path[2] = "<?=MODE?>";
								path[3] = slot.dataset.id;
							location.href = path.join("/");
						}
					});
				})(document.currentScript.parentNode);
				</script>
			</main>
			<?if(defined("ARG_2")):?>
			<section>
				<div class="tabs">
					<input id="task-tab" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="task" class="tab light-btn-bg" autocomplete="off">
						<?
						$statuses = $mySQL->getRow("SHOW COLUMNS FROM gb_task_shunter LIKE 'status'")['Type'];
						eval("\$statuses = ".preg_replace("/^enum/", "array", $statuses).";");

						$group = "";
						$staff = $mySQL->get("SELECT `CommunityID`,`Name`,`Group` FROM gb_staff CROSS JOIN gb_community USING(CommunityID) ORDER BY `Group`");

						$types = $mySQL->getGroup("SELECT type FROM gb_task_shunter GROUP BY type")['type'];

						$task = $mySQL->getRow("SELECT * FROM gb_task_timing CROSS JOIN gb_task_shunter USING(TaskID) WHERE TaskID={int}", ARG_2)?>
						<div class="h-bar dark-btn-bg">
							<small>Card № <input name="TaskID" value="<?=ARG_2?>" type="number" class="white-txt"></small>
							<small class="tool gold-txt" title="rank" data-translate="title">&#xe9d9;</small> <input name="rank" value="<?=$task['rank']?>" type="number" class="white-txt" title="rank" data-translate="title">
							<div class="select" title="performer" data-translate="title">
								<select name="performer" class="white-txt">
									<optgroup label="without group" data-translate="label">
										<option selected value="NULL" data-translate="textContent">not defined</option>
										<?foreach($staff as $user): if($user['Group']!=$group): $group=$user['Group']?>
									</optgroup>
									<optgroup label="<?=$group?>">
										<?endif?>
										<option <?if($user['CommunityID']==$task['CommunityID']):?>selected<?endif?> value="<?=$user['CommunityID']?>"><?=$user['Name']?></option>
										<?endforeach?>
									</optgroup>
								</select>
							</div>
							<div class="select" title="status" data-translate="title">
								<select name="status" class="white-txt">
									<?foreach($statuses as $status):?>
									<?if($task['status']==$status):?>
										<option selected value="<?=$status?>"><?=$status?></option>
									<?else:?><option <?if($status=="new"):?>disabled<?endif?> value="<?=$status?>"><?=$status?></option><?endif?>
									<?endforeach?>
								</select>
							</div>
							<!--<label for="screenmode" class="white-txt right">✕</label>-->
							<a class="tool right light-txt" href="/shunter/<?=MODE?>" title="close task" data-translate="title">✕</a>
						</div>
						<!--~~~~~-->

						
						<div class="toolbar l">
							<label title="get opengraph data" class="tool" data-translate="title"><input name="prowler" type="checkbox" hidden>&#xe905;</label>
						</div>
						<input name="link" value="<?=$task['link']?>" placeholder="link" data-translate="placeholder">

						<input name="image" value="<?=$task['image']?>" placeholder="Image URL">
						<div class="toolbar r">
							<label title="select image" class="tool" data-translate="title"><input name="imgbox" type="checkbox" hidden>&#xf07c;</label>
						</div>
						<div class="preview">
							<img src="<?=$task['image']?>" alt="" align="left">
							<textarea name="header" placeholder="header" data-translate="placeholder"><?=$task['header']?></textarea>
							<div class="input-with-select">
								<input name="type" value="<?=$task['type']?>" list="currenttasktypes" placeholder="type" data-translate="placeholder">
								<datalist id="currenttasktypes">
									<?foreach($types as $type):?>
									<option><?=$type?></option>
									<?endforeach?>
								</datalist>
							</div>
						</div>
						<div class="h-bar active-bg"><span data-translate="textContent">task</span></div>
						<textarea name="task" placeholder="task" data-translate="placeholder"><?=$task['task']?></textarea>
						<table width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
							<colgroup><col width="28"><col><col width="28"></colgroup>
							<tbody>
								<?foreach(JSON::parse($task['optionset']) as $row):?>
								<tr>
									<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
									<td contenteditable="true"><?=$row?></td>
									<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
								</tr>
								<?endforeach?>
								<tr>
									<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
									<td contenteditable="true"></td>
									<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
								</tr>
							</tbody>
						</table>
						<script>
						(function(form){
							var timeout,
								preview = form.querySelector("div.preview>img")
							form.onsubmit=function(event){ event.preventDefault(); }
							
							form.status.onchange = function(event){
								XHR.push({
									addressee:"/shunter/actions/change-status/<?=$task['TaskID']?>",
									body:JSON.encode({
										status:form.status.value,
										performer:<?=COMMUNITY_ID?>
									})
								});
							}
							form.performer.onchange = function(event){
								XHR.push({addressee:"/shunter/actions/change-performer/<?=$task['TaskID']?>/"+form.performer.value});
							}
							form.link.oninput =
							form.rank.oninput =
							form.type.oninput =
							form.task.oninput =
							form.header.oninput = function(event){
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									XHR.push({
										addressee:"/shunter/actions/change/<?=$task['TaskID']?>",
										body:'{"'+event.target.name+'":"'+event.target.value+'"}'
									});
								}, 1500);
							}
							form.image.oninput = function(event){
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									XHR.push({
										addressee:"/shunter/actions/change/<?=$task['TaskID']?>",
										body:'{"image":"'+form.image.value+'"}'
									});
								}, 500);
								preview.src = form.image.value;
							}
							form.prowler.onchange = function(){
								XHR.push({
									addressee:"/shunter/actions/prowler/<?=$task['TaskID']?>",
									body:form.link.value,
									onsuccess:function(response){
										try{
											response = JSON.parse(response);

											form.header.value = response['og:title'];
											preview.src = 
											form.image.value = response['og:image'];
										}catch(e){alert(response)}
									}
								});
							}
							form.imgbox.onchange = function(){
								new Box(null, "boxfather/imagebox",function(frm){
									form.image.value =
									preview.src = frm.querySelector("iframe").contentWindow.getSelectedURLs();
									XHR.push({
										addressee:"/shunter/actions/change/<?=$task['TaskID']?>",
										body:'{"image":"'+form.image.value+'"}'
									});
								});
							}
							form.TaskID.oninput = function(){
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									location.href = "/shunter/<?=MODE?>/"+form.TaskID.value;
								}, 1000);
							}
							form.querySelector("#currenttasktypes").onmousedown=function(event){
								this.previousElementSibling.value = event.target.textContent;
								XHR.push({
									addressee:"/shunter/actions/change/<?=$task['TaskID']?>",
									body:'{"type":"'+event.target.value+'"}'
								});
							}
							form.querySelector("table>tbody").oninput=function(event){
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									var properties=[];
									form.querySelectorAll("table>tbody>tr>td").forEach(function(cell){
										let val = cell.textContent.trim();
										if(val) properties.push(val);
									});
									XHR.push({
										addressee:"/shunter/actions/change-optionset/<?=$task['TaskID']?>",
										body:JSON.encode(properties)
									});
								},3000);
							}
						})(document.currentScript.parentNode);
						function deleteTask(TaskID){
							XHR.push({
								addressee:"/shunter/actions/remove/"+TaskID,
								onsuccess:function(response){
									if(parseInt(response)){
										let path = location.pathname.split(/\//);
											path.pop();
										location.href = path.join("/");
									}
								}
							});
						}
						</script>
					</form>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Task" class="tool" for="task-tab" data-translate="title">&#xe871;</label>
					<label title="delete task" data-translate="title" class="tool" onclick="deleteTask(<?=$task['TaskID']?>)">&#xe94d;</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						/*
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							STANDBY.rightbar = event.target.id;
						}});
						if(STANDBY.rightbar) bar[STANDBY.rightbar].checked = true;
						*/
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
			<?endif?>
		</div>
		<script>
		<?if(defined("ARG_2")):?>
		(function(body){
			//body.querySelector("#screenmode").checked = (STANDBY.screenmode=="true");
		})(document.currentScript.parentNode);
		<?endif?>
		</script>
	</body>
</html>