<?php
	/*
	$categories = $mySQL->get("SELECT CatID,category FROM gb_categories");
	foreach($categories as $category){
		$mySQL->inquiry("UPDATE gb_parts SET CatID={int} WHERE category={str}", $category['CatID'],$category['category']);
	}
	*/

	$categories = $mySQL->get("SELECT CatID,category FROM gb_categories");
	foreach($categories as $category){
		$mySQL->inquiry("UPDATE gb_categories SET category={str} WHERE CatID={int}", translite($category['category']), $category['CatID']);
	}

	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];

	$cng = new config("modules/challenger/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link type="text/css" rel="stylesheet" href="/modules/challenger/index.css">
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/challenger/index.js" defer></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" async charset="utf-8" onload="translate.fragment()"></script>
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

					<input id="lineups-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<form id="lineups" class="tab body-bg light-txt" autocomplete="off">
						<fieldset><legend>Sources</legend>
							<label><input name="silverlake" type="checkbox" value="https://www.silverlake.co.uk/includes/ajax" checked> Silverlake.co.uk</label>
							<label><input name="motorhog" type="checkbox" value="https://www.motorhog.co.uk/parts-breaking/find" disabled> Motorhog.co.uk</label>
						</fieldset>
						<fieldset><legend>Marks</legend>
							<?foreach($mySQL->get("SELECT * FROM gb_labels ORDER BY label") as $mark):?>
							<label>
								<img src="<?=$mark['logo']?>" align="left">
								<input type="radio" name="mark" value="<?=$mark['LabelID']?>" data-mark="<?=$mark['label']?>" hidden>
								<span><?=$mark['label']?></span><br>
								
							</label>
							<?endforeach?>
						</fieldset>
						<script>
						var CNG = {
							"MarkID":0,
							"LineID":0,
							"Stop":false,
							"Short Respite":<?=$cng->{"short respite"}['value']?>,
							"Respite":<?=$cng->{"prolonged respite"}['value']?>
						}
						var CNT = {
							Pages:0,
							Records:0
						}
						var QUERY = {
							code:"",
							type:"parts_cars",
							make:"",
							model:"",
							mark:"",
							sortorder:0,
							pgrq:1
						};
						var ADDED = [],
							ITEMS = [],
							THINGS = [],
							LINEUPS = []
							PAGINATION = [];
						(function(form){
							form.onchange=function(event){
									XHR.push({
										addressee:"/actions/challenger/gt_fragment",
										body:"https://www.silverlake.co.uk/includes/ajax/mms/?srch="+event.target.dataset.mark,
										onsuccess:function(response){
											CNG.MarkID = JSON.parse(response)[0].make;
											log.record("Get MarkID", event.target.value+" ["+CNG.MarkID+"]","u");
										}
									});
									XHR.push({
										addressee:"/actions/challenger/gt_lineups/"+event.target.value,
										onsuccess:function(response){
											log.interface(doc.create("template",{},response).content,"Start Import",function(frm){
												LINEUPS = Array.prototype.slice.call(frm.querySelectorAll("input:checked"));
												traversal();

												CNG.Stop = false;
												TOOLBAR.create("label",{
													class:"tool",
													title:"Abort Import",
													onclick:"CNG.Stop=true;this.parentNode.removeChild(this)"
												},"◼");
											});
										}
									});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="Lineups" class="tool" for="lineups-tab" data-translate="title">&#xe993;</label>
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
			<header class="h-bar light-txt logo-bg">
				<hr class="separator">
				<div class="toolbar t">
					<label class="tool" title="Lineups" onclick="new Box(null,'challenger/lineupsbox')">&#xeae3;</label>
					<label class="tool" title="categories" onclick="new Box(null,'challenger/catbox')">&#xe925;</label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main class="white-bg">
				<section id="log">

				</section>
				<section id="sublog">
					
				</section>
				<section class="h-bar">
					<div class="toolbar r">
						
					</div>
					<label title="settings" data-translate="title" class="tool">&#xf013;</label>
				</section>
				<script>
				var LOG,SUBLOG,TOOLBAR;
				(function(main){
					LOG = main.querySelector("#log");
					SUBLOG = main.querySelector("#sublog");
					TOOLBAR = main.querySelector("section.h-bar>div.toolbar");
				})(document.currentScript.parentNode)
				</script>
			</main>
		</div>
	</body>
</html>