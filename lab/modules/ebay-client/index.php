<?php
$staff = preg_split(
	"/,\s*/",
	JSON::load("modules/staff/config.init")['access']['value'],
	-1,
	PREG_SPLIT_NO_EMPTY
);
$settings = preg_split(
	"/,\s*/",
	JSON::load("modules/settings/config.init")['access']['value'],
	-1,
	PREG_SPLIT_NO_EMPTY
);

$access = [
	"staff"=>in_array(USER_GROUP, $staff),
	"settings"=>in_array(USER_GROUP, $settings)
];


$category = defined("ARG_1") ? ARG_1 : 0;
$offset = defined("ARG_2") ? ARG_2 : 1;
$limit = 21;

?>
<!DOCTYPE html>
<html>
	<head>
		<?(include_once "components/head.php")?>
		<link type="text/css" rel="stylesheet" href="/modules/ebay-client/index.css">
		<!--<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>-->
		<script src="/modules/ebay-client/index.js" defer></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=store" async charset="utf-8" onload="translate.fragment()"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="on" hidden>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar body-bg active-txt t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label for="rightbar-shower"></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="modules-tree" class="tab body-bg light-txt"><?( include_once "components/modules.php" )?></div>

					<input id="categories-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="categories" class="tab body-bg light-txt">
						<div class="h-bar white-txt">
							<span data-translate="textContent">categories</span>
						</div>
						<?
						$parts = $mySQL->getTree("CatID","ParentID","SELECT * FROM cb_categories");
						$activeCats = $mySQL->getGroup("SELECT CatID FROM cb_items GROUP BY CatID")['CatID'];
						$traversal = function($parent="0") use (
							&$parts,
							&$traversal,
							&$activeCats,
							$offset,
							$category
						){
							foreach($parts[$parent] as $key=>$val):?>
								<a class="<?if($val['CatID']==$category):?>active-txt<?elseif(in_array($val['CatID'], $activeCats)):?>published-txt<?else:?>empty-cat-txt<?endif?>" href="/ebay-client/<?=$val['CatID']?>"><?=json_decode($val['name'], true)['de']?></a>
								<?if(is_array($parts[$key])):?>
								<div class="root">
									<?$traversal($key)?>
								</div>
								<?endif?>
							<?endforeach;
						};

						$traversal();
						?>
						<script>
						(function(map){
							map.onscroll=function(){
								STANDBY.mapScrollTop = map.scrollTop;
							}
							map.scrollTop = STANDBY.mapScrollTop || 0;
						})(document.currentScript.parentNode)
						</script>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="categories" class="tool" for="categories-tab" data-translate="title">&#xe902;</label>
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
			<header class="h-bar light-txt body-bg">
				<div class="toolbar t">
					<label class="tool" title="Export to Excel File" onclick="new Box(null, 'ebay-client/exptoexcelbox')">&#xe925;</label>
					<label class="tool" title="Clear DataBase" onclick="XHR.push({addressee:'/ebay-client/actions/rm_all',onsuccess:function(){location.reload()} })">&#xe94d;</label>
				</div>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title"></label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main class="body-bg light-txt">
				<div id="tile">
					<?php

					if ($category > 0) {
						$subCategories = $mySQL->getGroup("SELECT CatID FROM cb_categories WHERE ParentID={int}", $category)['CatID'];
						if (is_array($subCategories)){
							$subCategories[] = $category;
						} else {
							$subCategories = [$category];
						}
						$where = $mySQL->parse("WHERE CatID IN ({arr})", $subCategories);
					} else {
						$where = "";
					}

					$feed = $mySQL->get("
						SELECT SQL_CALC_FOUND_ROWS
							cb_things.ThingID,
							named,
							preview,
							purchase,
							currency,
							selling,
							cb_brands.brand AS mark
						FROM
							cb_store
						JOIN
							cb_things USING(ThingID)
						JOIN
							cb_extended USING(ThingID)
						LEFT JOIN
							cb_brands USING(BrandID)
						{prp}
						ORDER BY
							cb_things.ThingID DESC
						LIMIT {int},{int}",
						$where,
						(($offset-1)*$limit),
						$limit
					);
					$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];

					foreach($feed as $item):?>
					<a class="snippet" data-id="<?=$item['ThingID']?>">
						<div class="preview">
							<img src="<?=$item['preview']?>" alt="">
						</div>
						<div class="header">
							<b class="active-txt"><?=$item['mark']?></b><br>
							<?=JSON::parse($item['named'])['de']?>
						</div>
						<div class="price" align="right"><span class="light-txt"><?=$item['purchase']?> <?=$item['currency']?></span> -> € <b class="published-txt"><?=$item['selling']?></b></div>
					</a>
					<?endforeach?>
					<script>
					(function(container){
						container.querySelectorAll("a").forEach(function(named){
							named.onclick = function(){
								var box = new Box(null, 'ebay-client/productbox/'+this.dataset.id);
							}
						});
					})(document.currentScript.parentNode)
					</script>
				</div>
				<div class="h-bar pagination white-txt" align="right">
				<?if(($total = ceil($count/$limit))>1):
					$root = "/ebay-client/".$category;

					if($offset>4):
						$j=$offset-2?>
						<a href="<?=$root?>/1">1</a> ...
					<?else: $j=1; endif;
					for(; $j<$offset; $j++):?><a href="<?=($root.'/'.$j)?>"><?=$j?></a><?endfor?>
					<span class="active-txt"><?=$j?></span>
					<?if($j<$total):?>
						<a href="<?=($root.'/'.(++$j))?>"><?=$j?></a>
						<?if($j<$total):?>
						<?if(($total-$j)>1):?> ... <?endif?>
						<a href="<?=($root.'/'.$total)?>"><?=$total?></a>
						<?endif?>
					<?endif;
				endif?>
				</div>
			</main>
			<section id="rightbar">
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="request" class="tab light-btn-bg" autocomplete="off">
						<div class="h-bar light-btn-bg" align="right">
							<div class="toolbar left">
								<span>eBay</span>
							</div>
							<div class="toolbar right">
								<small class="active-txt"><b>API:</b></small>
								<div class="select">
									<select name="api">
										<option value="searching">Searching</option>
										<option value="shopping" disabled>Shopping</option>
										<option value="trading" disabled>Trading</option>
									</select>
								</div>
								<div class="select">
									<select name="command">
										<option selected disabled>COMMAND :</option>
										<optgroup label="Searching">
											<option value="findItemsIneBayStores">Find in Stores</option>
											<option value="findItemsAdvanced" disabled>Advanced Find</option>
											<option value="findCompletedItems">Find Completed</option>
										</optgroup>
										<optgroup label="Shopping" disabled>
											<option value="GetItemStatus">GetItemStatus</option>
											<option value="GetMultipleItems">GetMultipleItems</option>
											<option value="GetUserProfile">GetUserProfile</option>
											<option value="GetCategoryInfo">GetCategoryInfo</option>
										</optgroup>
										<optgroup label="Trading" disabled>
											<option value="GetItem">GetItem</option>
											<option value="GetCategories">GetCategories</option>
										</optgroup>
									</select>
								</div>
							</div>
						</div>
						<section>
							<?if($category):
							$cat = $mySQL->getRow("SELECT * FROM cb_categories WHERE CatID={int} LIMIT 1", $category);
							$catName = json_decode($cat['name'], true);
							?>
							<div class="cat-options">
								<h2>
									Category ID:
									<output name="CatID" class="active-txt"><?=$cat['CatID']?></output>
									<div class="right">Level: <output name="level" class="active-txt"><?=$cat['Level']?></output></div>
								</h2>

								<fieldset>
									<legend>Category Name EN</legend>
									<input name="NameEN" value="<?=$catName['en']?>">
								</fieldset>
								<fieldset>
									<legend>Category Name DE</legend>
									<input name="NameDE" value="<?=$catName['de']?>">
								</fieldset>

								<fieldset class="left">
									<legend>Delivery Price</legend>
									<input name="dPrice" value="<?=$cat['delivery_price']?>" size="8" placeholder="€">
								</fieldset>
								<fieldset class="left">
									<legend>Status</legend>
									<div class="select" name="status">
										<select name="status">
											<option value="enabled">Enabled</option>
											<option <?if($cat['status']=='disabled'):?>selected<?endif?> value="disabled">Disabled</option>
										</select>
									</div>
								</fieldset>
								<fieldset>
									<legend>SLUG</legend>
									<input name="slug" value="<?=$cat['slug']?>" required>
								</fieldset>
							</div>
							<div align="right">
								<button class="dark-btn-bg">save</button>
							</div>
							<script>
							(function(container){
								var form = container.parentNode;
								form.onsubmit = function(event){
									event.preventDefault();
									XHR.push({
										addressee:"/ebay-client/actions/sv_category/"+form.CatID.value,
										body: JSON.encode({
											delivery_price: form.dPrice.value,
											status: form.status.value,
											slug: form.slug.value.trim(),
											NameEN: form.NameEN.value.trim(),
											NameDE: form.NameDE.value.trim()
										}),
										onsuccess:function(response){

										}
									});
								}
							})(document.currentScript.parentNode)
							</script>
							<?else: $traversal(); endif?>
						</section>
						<script>
						(function(form){
							form.command.onchange=function(){
								XHR.push({
									addressee:"/ebay-client/router/gt_form/<?=$category?>",
									body:form.command.value,
									onsuccess:function(response){
										let section = form.querySelector("section");
										section.innerHTML = response;
										section.querySelectorAll("script").forEach(function(sct){
											var script = document.createElement("script");
												script.innerHTML = sct.innerHTML;
												sct.parentNode.replaceChild(script, sct);
										});
									}
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Request" class="tool" for="right-default">&#xe871;</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
		</div>
	</body>
</html>
