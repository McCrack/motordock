<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];

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
		JSON::save("patterns/json/checkboard.json",JSON::encode($checkboard));
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/checkboard/index.css">
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/xhr/wordlist?d[0]=base&d[1]=modules&d[2]=shunter" async charset="utf-8" onload="translate.fragment()"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden>
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
				<div class="toolbar l">
					<label class="tool" title="create board" data-translate="title" onclick="createBoard()">&#xe89c;</label>
					<label class="tool" title="show pattern" data-translate="title" onclick="showPatternBox()">⌘</label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
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
				function createBoard(){
					promptBox("board name",function(form){
						XHR.push({
							addressee:"/checkboard/actions/create-board",
							body:form.field.value.trim().replace(/"/g,"”"),
							onsuccess:function(response){
								doc.querySelector("#wrapper>main>form").innerHTML += response;
							}
						});
					},["dark-btn-bg"]);
				}
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
				function showPatternBox(){
					var pedt;
					var pattern = new Box("patterns/json/checkboard.json", "patterns/json_patternbox");
					pattern.onopen=function(){
						pedt = ace.edit(pattern.window.querySelector("main"));
						pedt.setTheme("ace/theme/solarized_dark");
						pedt.getSession().setMode("ace/mode/json");
						pedt.setShowInvisibles(false);
						pedt.setShowPrintMargin(false);
						pedt.resize();
					}
					pattern.onsubmit = function(form){
						editor.session.insert(editor.selection.getCursor(), pedt.session.getValue());
						noChanged = false;
						window.parent.document.querySelector("#wrapper>header>div.tabbar>label[for='"+frame_handle+"']").classList.toggle("changed", true);
					}
				}
				</script>
				</form>
			</main>
		</div>
	</body>
</html>