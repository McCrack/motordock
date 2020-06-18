<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	
	define("CATEGORY", defined("ARG_1") ? ARG_1 : false);

	//$cng = new config("../".BASE_FOLDER."/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" async charset="utf-8" onload="translate.fragment()"></script>
		<style>
		#wrapper>header .select{
			height:28px;
			padding-left:5px;
			border-radius:3px;
			box-shadow:inset 0 0 5px -2px rgba(0,0,0, .6);
			background-image:linear-gradient(to top, #FFF, #EEE);
		}
		</style>
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
			<header class="h-bar logo-bg">
				<div class="toolbar l">
					<label class="tool" title="Add Record" onclick="addRecord()">&#xe146;</label>
					<div class="select">
						<select name="category" autocomplete="off">
							<?if(!CATEGORY):?><option disabled selected>not defined</option><?endif?>
							<?foreach($mySQL->getGroup("SELECT category FROM gb_redirects GROUP BY category")['category'] as $category):?>
							<option <?if($category==CATEGORY):?>selected<?endif?> value="<?=$category?>"><?=$category?></option>
							<?endforeach?>
							<script>
							(function(select){
								select.onchange=function(){
									location.href = "/redirector/"+select.value;
								}
							})(document.currentScript.parentNode)
							</script>
						</select>
					</div>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main>
				<table width="100%" cellspacing="0" cellpadding="5" rules="cols" bordercolor="#CCC">
					<thead>
						<tr class="light-btn-bg">
							<th width="36">ID</th>
							<th width="80">Category</th>
							<th>Source</th>
							<th>Target</th>
							<th width="86">HTTP Code</th>
							<th width="30"></th>
						</tr>
					</thead>
					<tbody>
						<?if(CATEGORY) foreach($mySQL->get("SELECT * FROM gb_redirects WHERE category LIKE {str} ORDER BY ID DESC", CATEGORY) as $itm):?>
						<tr data-id="<?=$itm['ID']?>">
							<td align="center"><?=$itm['ID']?></td>
							<td contenteditable="true" title="category"><?=$itm['category']?></td>
							<td contenteditable="true" title="source"><?=$itm['source']?></td>
							<td contenteditable="true" title="target"><?=$itm['target']?></td>
							<td contenteditable="true" title="code" align="center"><?=$itm['code']?></td>
							<th class="tool drop-row" title="Delete Record" onclick="deleteRecord(this.parentNode)">✕</th>
						</tr>
						<?endforeach?>
						<script>
						(function(body){
							var timeout;
							body.oninput=function(event){
								clearTimeout(timeout);
								timeout = setTimeout(function(){
									XHR.push({
										addressee:"/redirector/actions/change/"+event.target.parentNode.dataset.id,
										body:'{"'+event.target.title+'":"'+event.target.textContent+'"}'
									});
								},2000);
							}
						})(document.currentScript.parentNode)	
						</script>
					</tbody>
				</table>
				<script>
				var addRecord=function(){
					XHR.push({
						addressee:"/redirector/actions/add-record",
						onsuccess:function(response){
							if(parseInt(response)){
								let row = doc.create("tr",{"data-id":response});
								[
									doc.create("td",{align:"center"},response),
									doc.create("td",{contenteditable:"true",title:"category"}),
									doc.create("td",{contenteditable:"true",title:"source"}),
									doc.create("td",{contenteditable:"true",title:"target"}),
									doc.create("td",{contenteditable:"true",title:"code",align:"center"},"301"),
									doc.create("th",{calss:"tool drop-row",title:"Delete Record",onclick:"deleteRecord(this.parentNode)"},"✕")
								].forEach(function(cell){
									row.appendChild(cell);
								});
								doc.querySelector("main>table>tbody").insertToBegin(row);
							}
						}
					});
				}
				var deleteRecord=function(row){
					XHR.push({
						addressee:"/redirector/actions/delete/"+row.dataset.id,
						onsuccess:function(response){
							if(parseInt(response)) row.parentNode.removeChild(row);
						}
					});
				}
				</script>
			</main>
		</div>
	</body>
</html>