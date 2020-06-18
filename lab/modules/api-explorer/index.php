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

	$cng = JSON::load("modules/api-explorer/config.init");
?>
<!DOCTYPE html>
<html>
	<head>
		<?( include_once "components/head.php" )?>
		<link type="text/css" rel="stylesheet" href="/modules/api-explorer/index.css">
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules" async charset="utf-8" onload="translate.fragment()"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden>
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar active-bg t">
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
			<header class="h-bar light-txt active-bg">
				<div class="select">
					<select name="format" class="white-txt" autocomplete="off">
						<option value="JSON">JSON</option>
						<option value="XML">XML</option>
						<option value="plain">Plain Text</option>
						<script>
						document.currentScript.parentNode.onchange=function(){
							CODE.getSession().setMode("ace/mode/"+this.value.toLowerCase());
						}
						</script>
					</select>
				</div>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<main class="body-bg white-txt">
				<xmp></xmp>
				<script>
				var	CODE = ace.edit(document.currentScript.parentNode.querySelector("xmp"));
					CODE.setTheme("ace/theme/twilight");
					CODE.getSession().setMode("ace/mode/json");
					CODE.setShowInvisibles(false);
					CODE.setShowPrintMargin(false);
					CODE.resize();
				</script>
			</main>
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<form id="request" class="tab light-btn-bg" autocomplete="on">
						<div class="h-bar" align="right">
							<div class="toolbar left">
								<span>Resource:</span>
								<div class="select">
									<select name="resource">
										<option value="eBay">eBay</option>
									</select>
								</div>
							</div>
							<div class="toolbar right">
								<span>API:</span>
								<div class="select">
									<select name="api">
										<option valsue="searching">Searching</option>
										<option valsue="trading">Trading</option>
										<option valsue="shopping">Shopping</option>
										<optgroup label="Sell APIs" disabled>
											<option valsue="account">Account</option>
											<option valsue="inventory">Inventory</option>
											<option valsue="fulfillment">Fulfillment</option>
											<option valsue="finances">Finances</option>
											<option valsue="marketing">Marketing</option>
											<option valsue="recommendation">Recommendation</option>
											<option valsue="analytics">Analytics</option>
											<option valsue="metadata">Metadata</option>
											<option valsue="compliance">Compliance</option>
											<option valsue="logistics">Logistics</option>
										</optgroup>
										<optgroup label="Buy APIs" disabled>
											<option valsue="feed">Feed</option>
											<option valsue="browse">Browse</option>
											<option valsue="marketing">Marketing</option>
											<option valsue="offer">Offer</option>
											<option valsue="order">Order</option>
											<option valsue="marketplace-insights">Marketplace Insights</option>
										</optgroup>
										<optgroup label="Commerce APIs" disabled>
											<option valsue="catalog">Catalog</option>
											<option valsue="identity">Identity</option>
											<option valsue="taxonomy">Taxonomy</option>
											<option valsue="translation">Translation</option>
										</optgroup>
										<optgroup label="Developer APIs" disabled>
											<option valsue="analytics">Analytics</option>
										</optgroup>
										<optgroup label="Shopping APIs" disabled>
											<option valsue="shopping">Shopping</option>
											<option valsue="merchandising">Merchandising</option>
										</optgroup>
										<optgroup label="Selling APIs" disabled>
											<option valsue="trading">Trading</option>
											<option valsue="platform-notifications">Platform Notifications</option>
											<option valsue="product-services">Product Services</option>
											<option valsue="client-alerts">Client Alerts</option>
										</optgroup>
										<optgroup label="After Sale APIs" disabled>
											<option valsue="post-order">Post Order</option>
											<option valsue="feedback">Feedback</option>
										</optgroup>
									</select>
								</div>
							</div>
						</div>
						<section>
							<input name="command" placeholder="Command" list="command" required>
							<button class="dark-btn-bg">execute</button>
							<datalist id="command">
								<optgroup label="Searching">
									<option value="findItemsAdvanced">findItemsAdvanced</option>
									<option value="findCompletedItems">findCompletedItems</option>
									<option value="findItemsIneBayStores">findItemsIneBayStores</option>
								</optgroup>
								<optgroup label="Trading">
									<option value="GetItem">GetItem</option>
									<option value="GetCategories">GetCategories</option>
								</optgroup>
								<optgroup label="Shopping">
									<option value="GetItemStatus">GetItemStatus</option>
									<option value="GetMultipleItems">GetMultipleItems</option>
									<option value="GetUserProfile">GetUserProfile</option>
									<option value="GetCategoryInfo">GetCategoryInfo</option>
								</optgroup>
							</datalist>
							<br><br>
							<table width="100%" rules="cols" cellspacing="0" cellpadding="5" bordercolor="#BBB">
								<col width="25"><col><col width="25">
								<tbody>
									<tr>
										<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
										<td contenteditable="true"></td>
										<th></th>
									</tr>
								</tbody>
							</table>
						</section>
						<script>
						(function(form){
							form.onsubmit=function(event){
								event.preventDefault();
								XHR.push({
									addressee:"/actions/api-explorer/"+form.resource.value,
									body:JSON.encode({
										api:form.api.value,
										resource:form.resource.value,
										command:form.command.value,
										fields:(function(fields){
											form.querySelectorAll("table>tbody>tr>td").forEach(function(cell, i){
												fields.push(cell.textContent.trim());
											});
											return fields
										})([])
									}),
									onsuccess:function(response){
										CODE.session.setValue(response)
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
