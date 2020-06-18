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

	$cng = JSON::load("modules/ebay-import/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link type="text/css" rel="stylesheet" href="/modules/ebay-import/index.css">
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/ebay-import/index.js" defer></script>
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
			<header class="h-bar light-txt logo-bg">
				<hr class="separator">
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main class="body-bg white-txt">
				<xmp><?php
				require_once("core/api/eBay/index.php");

				$client = eBay::shopping("GetUserProfile");
				//$client->query->UserID = "synetiq";
				//$response = $client->post("XML");
				$response = $client->GetUserProfile("synetiq")->post("XML");
				//$response = $client->GetCategoryInfo(33559,77);
				if($response->code!=200){
					print "HTTP CODE: ".$response->code."\n";
					print_r($response->data);
				}else{
					$xml = new \SimpleXMLElement($response->data);
					print_r($xml);
				}
				?>
				</xmp>
				<form class="light-btn-bg" autocomplete="on">
					<input name="api" placeholder="API" list="api" required>
					<input name="command" placeholder="Command" list="command" required>
					
					<button class="dark-btn-bg">execute</button>
					<datalist id="api">
						<option value="searching">Searching</option>
						<option value="trading">Trading</option>
						<option value="shopping">Shopping</option>
					</datalist>
					<datalist id="command">
						<option value="getVersion">getVersion</option>
						<option value="GetSessionIDRequest">GetSessionIDRequest</option>
					</datalist>
					<script>
					(function(form){
						var editor = 
						form.onsubmit=function(event){
							event.preventDefault();
							XHR.push({
								addressee:"/actions/ebay-import/"+form.api.value,
								body:JSON.encode({
									command:form.command.value
								}),
								onsuccess:function(response){
									CODE.session.setValue(response)
								}
							});
						}
					})(document.currentScript.parentNode)
					</script>
				</form>
				<script>
				
				var	CODE = ace.edit(document.currentScript.parentNode.querySelector("xmp"));
					CODE.setTheme("ace/theme/twilight");
					CODE.getSession().setMode("ace/mode/json");
					CODE.setShowInvisibles(false);
					CODE.setShowPrintMargin(false);
					CODE.resize();
				
				</script>
			</main>
		</div>
	</body>
</html>