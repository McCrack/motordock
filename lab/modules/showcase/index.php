<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	
	/*
	ARG_1 - Language
	ARG_2 - PageID OR Category Name
	ARG_3 - ItemID OR Filterset
	ARG_4 - Pagination
	*/

	$limit = 30;

	if(defined("ARG_2")){
		if(is_numeric(ARG_2)){
			
			$model = $mySQL->getRow("
			SELECT * FROM gb_pages
			CROSS JOIN gb_models USING(PageID)
			LEFT JOIN gb_labels USING(LabelID)
			WHERE gb_pages.PageID = {int} LIMIT 1", ARG_2);

			if($mySQL->status['affected_rows']>0){
				define("PRODUCT", $model['PageID']);
				define("CATEGORY", $model['CategoryID']);

				$category = $mySQL->getRow("SELECT filterset,optionset FROM gb_static WHERE PageID={int} LIMIT 1", CATEGORY);

			}else{
				header("Location: /showcase",true,301);
				exit;
			}

			if(defined("ARG_3") && is_numeric(ARG_3)){
				$item = $mySQL->getRow("SELECT * FROM gb_items LEFT JOIN gb_discounts USING(DiscountID) WHERE ItemID={int} LIMIT 1", ARG_3);
			}else $item = $mySQL->getRow("SELECT * FROM gb_items LEFT JOIN gb_discounts USING(DiscountID) WHERE ItemID={int} LIMIT 1", $model['ItemID']);

			$remainder = $mySQL->getRow("SELECT SUM(remainder) AS remainder FROM gb_stock WHERE ItemID = {int} LIMIT 1", $item['ItemID'])['remainder'];
		}else{

			$offset = defined("ARG_4") ? ARG_4 : 1;

			$category = $mySQL->getRow("
				SELECT
					PageID,
					filterset,
					optionset,
					name,
					header
				FROM
					gb_sitemap
				CROSS JOIN
					gb_static USING(PageID)
				WHERE
					language LIKE {str}
					AND name LIKE {str}
				LIMIT 1",
				ARG_1,
				ARG_2
			);
			
			if($mySQL->status['affected_rows']>0){

				define("PRODUCT", false);
				define("CATEGORY", $category['PageID']);

				if (defined("ARG_3")) {

					$set = ["CategoryID={int}"];
					$filterset = [];
					$cells = explode("-", ARG_3);
					
					foreach($cells as $cell){
						$filterset[] = explode("x",$cell);
						if($cell) $set[] = "(FilterID IN (".implode(",",end($filterset))."))";
					}

					$feed = $mySQL->get("
						SELECT
							SQL_CALC_FOUND_ROWS *
						FROM
							item_vs_filters
						CROSS JOIN
							gb_models USING(PageID)
						WHERE
							".implode(" AND ", $set)."
						LIMIT
							{int},{int}",
						CATEGORY,
						(($offset-1)*$limit),
						$limit
					);
				} else {
					$feed = $mySQL->get("
						SELECT 
							SQL_CALC_FOUND_ROWS * 
						FROM 
							gb_models 
						WHERE 
							CategoryID={int} 
						LIMIT
							{int},{int}",
						CATEGORY,
						(($offset-1)*$limit),
						$limit
					);	
				}

				$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];
			}else{
				header("Location: /showcase",true,301);
				exit;
			}
		}
	}else{
		define("PRODUCT", false);
		define("CATEGORY", false);
	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/showcase/index.css">
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/code-editor/tpl/code-editor.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=store" defer charset="utf-8"></script>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" hidden onchange="STANDBY.screenmode=this.checked">
		<div id="wrapper">
			<input id="leftbar-shower" type="checkbox" autocomplete="off" hidden>
			<input id="rightbar-shower" type="checkbox" autocomplete="off" hidden>
			<nav class="h-bar light-txt t">
				<label for="leftbar-shower"></label>
				<a href="/" id="goolybeep">G</a>
				<label for="rightbar-shower"></label>
			</nav>
			<aside class="body-bg">
				<div class="tabs">
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="categories-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="categories" class="tab body-bg light-txt">
						<div class="h-bar white-txt">
							<span data-translate="textContent">categories</span>
							<div class="select right">
								<select class="active-txt">
									<?php 
									$language = defined("ARG_1") ? ARG_1 : $cng->language;
									foreach($cng->languageset as $lang):?>
										<option <?if($lang === $language):?>selected<?endif?> value="<?=$lang?>"><?=$lang?></option>
									<?endforeach?>
									<script>
									(function(select){
										select.onchange=function(){
											LANGUAGE = reloadTree(select.value);
										}
									})(document.currentScript.parentNode);
									</script>
								</select>
							</div>
						</div>

						<a class="root-itm light-txt" href="/showcase" data-translate="textContent">showcase</a>
						<?function staticTree(&$items, $offset="root"){
							if(is_array($items[$offset])):?>
							<div class="root">
								<?foreach($items[$offset] as $key=>$val):?>
								<a href="/showcase/<?=$val['language']?>/<?=$val['name']?>" class="<?if($val['PageID']==CATEGORY):?>active-txt<?elseif($val['published']==='Published'):?>published-txt<?endif?>">
									<?=(empty($val['header'])?$val['name']:$val['header'])?>
								</a>
								<?staticTree($items, $key);
								endforeach?>
							</div>
							<?endif;
						}
						$tree = $mySQL->getTree("name", "parent", "SELECT * FROM gb_sitemap WHERE language LIKE {str} ORDER BY SortID ASC", $language);
						staticTree($tree, "showcase")?>
						<script>
						(function(map){
							map.onscroll=function(){STANDBY.mapScrollTop = map.scrollTop;}
						})(document.currentScript.parentNode)
						</script>
					</div>
					<?if(PRODUCT):?>


					<?elseif(CATEGORY):?>
					<input id="filters-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<form id="filters" class="tab body-bg light-txt" autocomplete="off">
						<div class="h-bar white-txt" data-translate="textContent">filters</div>
						<?$i=0;
						$fPattern = JSON::parse($category['filterset']);
						foreach($fPattern as $setname=>$set):?>
						<fieldset><legend><?=$setname?></legend>
							<?foreach($set as $id=>$value):?>
							<label><input value="<?=$id?>" <?if(in_array($id, $filterset[$i])):?>checked<?endif?> type="checkbox" hidden><span><?=$value?></span></label>
							<?endforeach?>
						</fieldset>
						<? $i++; endforeach?>
						<script>
						(function(form){
							form.onchange=function(){
								var filters = [];
								form.querySelectorAll("fieldset").forEach(function(set,i){
									var filterset = [0];
									set.querySelectorAll("input:checked").forEach(function(inp){
										filterset.push(inp.value);
									});
									filters.push(filterset.join("x"));
								});
								location.pathname = "/showcase/"+LANGUAGE+"/<?=$category['name']?>/"+filters.join("-");
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
					<?endif?>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="categories" class="tool" for="categories-tab" data-translate="title">&#xe902;</label>
						<?if(PRODUCT):?>

						<?elseif(CATEGORY):?>
						<label title="filters" class="tool" for="filters-tab" data-translate="title">&#xe5d1;</label>
						<?endif?>
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
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							if(event.target.id!="left-default") STANDBY.leftbar = event.target.id;
						}});
    					if(STANDBY.leftbar && bar[STANDBY.leftbar]) bar[STANDBY.leftbar].checked = true;
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar light-txt">
				<?if(PRODUCT):?><a class="tool" title="Back" href="/showcase">❬</a><?endif?>
				<?if(defined("ARG_1")):?>
				<div class="toolbar t">
					<label title="create model" class="tool" data-translate="title" onclick="new Box(null, 'showcase/createbox/<?=CATEGORY?>/'+LANGUAGE)">&#xe89c;</label>
				</div>
				<?endif?>
				<?if(PRODUCT):?>
				<div class="toolbar t">
					<label title="save" class="tool" data-translate="title" onclick="saveAll()">&#xf0c7;</label>
				</div>
				<?endif?>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<?if(defined("ARG_2")):?>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<?endif?>
			</header>
			<main>
			<?if(PRODUCT):
				function catlist(&$items, $selected, $offset="showcase"){
					if(is_array($items[$offset])): foreach($items[$offset] as $key=>$val):?>
					<option <?if($val['PageID']==$selected):?>selected<?endif?> value="<?=$val['PageID']?>"><?=$val['PageID']?>. <?=(empty($val['header'])?$val['name']:$val['header'])?></option>
					<?catlist($items, $selected, $key);
					endforeach; endif;
				}?>
				<fieldset class="left"><legend data-translate="textContent" class="light-txt">category</legend>
					<div class="select">
						<select name="CategoryID" class="active-txt" autocomplete="off" form="properties">
							<?catlist($tree, $model['CategoryID'])?>
						</select>
					</div>
				</fieldset>
				<fieldset class="left"><legend data-translate="textContent" class="light-txt">related category</legend>
					<div class="select">
						<select name="RelCatID" class="active-txt" autocomplete="off" form="properties">
							<?catlist($tree, $model['RelCatID'])?>
						</select>
					</div>
				</fieldset>
				<fieldset class="right"><legend data-translate="textContent" class="gold-txt">template</legend>
					<div class="select text-field">
						<select name="subtemplate" autocomplete="off" form="properties">
							<?foreach(glob("../".BASE_FOLDER."/themes/".$cng->theme."/includes/showcase/*.html") as $file):$file = pathinfo($file)['filename']?>
							<option <?if($file==$model['subtemplate']):?>selected<?endif?> value="<?=$file?>"><?=$file?></option>
							<?endforeach?>
						</select>
					</div>
				</fieldset>
				
				<!--~~~ MEDIASET ~~~~-->
				<section id="mediaset">
					<div id="slideshow">
						<?$mediaset = JSON::parse($model['mediaset']);
						foreach($mediaset as $itm):?>
						<form autocomplete="off">
							<fieldset>
								<label class="left"><input type="checkbox" hidden <?if($itm['hidden']=="NO"):?>checked<?endif?> name="hidden"><span>&#xf011;</span></label>
								
								<label><input type="radio" hidden <?if($itm['position']=="top"):?>checked<?endif?> name="position" value="top"><span>&#xe86b;</span></label>
								<label><input type="radio" hidden <?if($itm['position']=="bottom"):?>checked<?endif?> name="position" value="bottom"><span>&#xe3c8;</span></label>
								<label><input type="radio" hidden <?if($itm['color']=="light"):?>checked<?endif?> name="color" value="light"><span>&#xf069;</span></label>
								<label><input type="radio" hidden <?if($itm['color']=="dark"):?>checked<?endif?> name="color" value="dark"><span>&#xe94e;</span></label>
								<br>
								<input value="<?=$itm['alt']?>" placeholder="Alternative Text" name="alt">
								<br>
								<textarea placeholder="Description" name="description"><?=$itm['description']?></textarea>
							</fieldset>
							<?if($itm['type']=="video"):?>
							<video src="<?=$itm['url']?>" controls></video>
							<?else:?>
							<img src="<?=$itm['url']?>">
							<?endif?>
						</form>
						<?endforeach?>
						<script>var SLIDER = document.currentScript.parentNode</script>
					</div>

					<div id="slidelist">
						<?foreach($mediaset as $itm):?>
						<div class="card">
							<label class="drop-card">✕</label>
							<div class="preview">
								<?if($itm['type']=="video"):?>
								<video src="<?=$itm['url']?>" preload="metadata"></video>
								<?else:?>
								<img src="<?=$itm['url']?>" alt="&#xe94a;">
								<?endif?>
							</div>
						</div>
						<?endforeach?>
						<script>
						
						var	MEDIASET = document.currentScript.parentNode;
							MEDIASET.refresSlideshow = function(){
							MEDIASET.querySelectorAll(".card").forEach(function(img,i){
								img.onmouseover=function(){
									SLIDER.shotSlide(i*SLIDER.offsetWidth);
								}
							});
							MEDIASET.querySelectorAll("label").forEach(function(label,i){
								label.onclick=function(){
									SLIDER.removeChild( SLIDER.querySelectorAll("form")[i] );
									MEDIASET.removeChild( label.parentNode);
									MEDIASET.refresSlideshow();
								}
							});
						}
						MEDIASET.refresSlideshow();
						</script>
					</div>

					<form title="<?=$mediaset['Category']?> / <?=$mediaset['Name']?>">
						<button name="previous" id="left-btn" data-dir="-1" class="transparent-bg">❰</button>
						<button name="next" id="right-btn" data-dir="1" class="transparent-bg">❱</button>
						<button name="add" id="add-btn">add</button>
						<template id="slide-tpl">
							<fieldset>
								<label class="left"><input type="checkbox" hidden name="showdesc"><span>&#xf011;</span></label>

								<label><input type="radio" hidden checked name="position" value="top"><span>&#xe86b;</span></label>
								<label><input type="radio" hidden name="position" value="bottom"><span>&#xe3c8;</span></label>
								<label><input type="radio" hidden checked name="color" value="light"><span>&#xf069;</span></label>
								<label><input type="radio" hidden name="color" value="dark"><span>&#xe94e;</span></label>
								<br><input placeholder="Alternative Text" name="alt">
								<br><textarea placeholder="Description" name="description"></textarea>
							</fieldset>
						</template>
						<script>
						(function(form){
							var animate;
							form.onsubmit=function(event){
								event.preventDefault();
							}
							form.next.onclick=
							form.previous.onclick=function(event){
								event.preventDefault();
								let dir = parseInt(event.target.dataset.dir),
								offset = SLIDER.offsetWidth*(dir+(SLIDER.scrollLeft/SLIDER.offsetWidth)>>0);

								if((offset<0) || offset>(SLIDER.scrollWidth-SLIDER.offsetWidth)) return false;
								SLIDER.shotSlide(offset);
							}
							SLIDER.shotSlide = function(offset){
								cancelAnimationFrame(animate);
								animate = requestAnimationFrame(function scrollSlide(){
									if(Math.abs(offset - SLIDER.scrollLeft) > 16){
										SLIDER.scrollLeft += (offset - SLIDER.scrollLeft)/8;
										animate = requestAnimationFrame(scrollSlide);
									}else SLIDER.scrollLeft = offset;
								});
							}
							form.add.onclick=function(event){
								event.preventDefault();
								window.parent.openBox('{}', "mediaset/navigatorbox",function(box){
									box.querySelector(".box-body>iframe").contentWindow.getSelected().forEach(function(img){

										let frm = doc.create("form",{autocomplete:"off"}, form.querySelector("#slide-tpl").cloneNode(true).content);
										switch(img.type){
											case "image":
												var slide = doc.create("img",{src:img.url,alt:""});
												frm.appendChild(slide.cloneNode(true));
											break;
											case "video":
												frm.appendChild( doc.create("video",{src:img.url,controls:"true"}) );
												var slide = doc.create("video",{src:img.url});
											break;
											default:break;
										}
										SLIDER.appendChild(frm);
										let snippet = doc.create("div", {class:"card"}, "<label class='drop-card'>✕</label>");
										let preview = doc.create("div", {class:"preview"});
											preview.appendChild( slide );
										snippet.appendChild( preview );
										MEDIASET.appendChild(snippet);
									});
									MEDIASET.refresSlideshow();
									box.drop();
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
				</section>
					
				<!--~~~ DESCRIPTION ~~~~-->
				<iframe src="/editor/embed" width="100%" height="100%" frameborder="no"></iframe>
				<script>var EDITOR = document.currentScript.previousElementSibling.contentWindow;</script>
				
				<?elseif(CATEGORY):?>
				<div id="tile">
					<?foreach($feed as $snippet):?>
					<a class="snippet" href="/showcase/<?=$language?>/<?=$snippet['PageID']?>/<?=$snippet['ItemID']?>">
						<div class="preview"><img src="<?=$snippet['preview']?>" alt="&#xe906;"></div>
						<div class="header"><?=$snippet['named']?></div>
					</a>
					<?endforeach?>
				</div>
				<div class="h-bar pagination white-txt" align="right">
				<?if(($total = ceil($count/$limit))>1):
					$root = "/showcase/".$category['name']."/".(defined("ARG_2")?ARG_2:0);

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
				<?endif?>
			</main>
			<?if(defined("ARG_2")):?>
			<section>
				<div class="tabs">
				<?if(PRODUCT):?>

				<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
				<div id="metadata" class="tab body-bg light-txt">
					<div class="h-bar light-bg">
						ID: <span class="white-txt"><?=$model['PageID']?></span> / <span class="active-txt"><?=$item['ItemID']?></span>
						
						<hr class="separator">
						<!--~~~ Available ~~~-->
						<div class="select">
							<select name="status" class="active-txt" form="properties">
								<?foreach(["available","not available"] as $status):?>
								<option <?if($status==$model['status']):?>selected<?endif?> data-translate="textContent" value="<?=$status?>"><?=$status?></option>
								<?endforeach?>
							</select>
						</div>

						<hr class="separator">
						<!--~~~ Reference ~~~-->
						<label>
							<input name="reference" type="radio" form="properties" <?if($model['ItemID']==$item['ItemID']):?>checked<?endif?> hidden autocomplete="off">
							<small data-translate="textContent">reference</small>
						</label>
						
						<!--~~ Item Select ~~-->
						<div class="toolbar t right">
							<div class="select">
								<select name="items" class="active-txt" autocomplete="off" form="properties">
									<?foreach($mySQL->get("SELECT ItemID,item FROM gb_items WHERE PageID={int}", $model['PageID']) as $row):?>
									<option <?if($row['ItemID']==$item['ItemID']):?>selected<?endif?> value="<?=$row['ItemID']?>"><?=$row['item']?></option>
									<?endforeach?>
									
									<script>
									(function(select){
										select.onchange=function(){
											let path = location.pathname.split("/");
												path[4] = select.value;
											location.pathname = path.join("/");
										}
									})(document.currentScript.parentNode)
									</script>
								</select>
							</div>

							<!--~~ Add Item Button ~~-->
							<label for="add-itm-btn" title="add item" class="tool" data-translate="title">&#xe146;</label>
							<input type="checkbox" name="saved" autocomplete="off" form="properties" hidden><span title="autosave indicator" class="tool">&#xf0c7;</span>
						</div>
					</div>
					<div id="cover">
						<iframe frameborder="no"></iframe>
						<script>
						(function(script){
							var frame =  script.previousElementSibling;
							var	navigator = frame.contentWindow, options = [];
							navigator.standby = (window.localStorage['navigator'] || "undefined").jsonToObj() || {};

							if(navigator.standby.subdomain) options.push("subdomain="+navigator.standby.subdomain);
							if(navigator.standby[navigator.standby.subdomain]) options.push("path="+navigator.standby[navigator.standby.subdomain]);

							window.addEventListener("load",function(){
								reauth();
								navigator.location.href="/navigator/folder/image/radio?"+options.join("&");
								frame.onload=function(){
									navigator.onchange=function(event){
										if(event.target.name=="files-on-folder"){
											script.nextElementSibling.src=event.target.value;

											XHR.push({
												addressee:"/actions/showcase/ch_model/<?=$model['PageID']?>/preview",
												body:utf8_to_b64(event.target.value),
												onsuccess:function(response){
													if(parseInt(response)){

													}
												}
											});
										}
									}
								}
							});
						})(document.currentScript)
						</script>
						<img src="<?=$model['preview']?>" alt="&#xe94a;">
					</div>
					<form id="properties" autocomplete="off">
						<!--~~~~ Named ~~~~-->
						<fieldset style="grid-area:1/1/3/3;">
							<legend class="white-txt" data-translate="textContent">model</legend>
							<textarea name="named" placeholder="..." class="text-field"><?=$model['named']?></textarea>
						</fieldset>

						<!-- Purchase Price -->
						<fieldset><legend data-translate="textContent" class="active-txt">purchase</legend>
							<input name="purchase" value="<?=$item['purchase']?>" size="5" placeholder="0.00" class="text-field">
						</fieldset>

						<!--~~ Discount ~~-->
						<fieldset><legend data-translate="textContent" class="green-txt">discount</legend>
							<input name="discount" value="<?=$item['sticker']?>" data-id="<?=$item['DiscountID']?>" placeholder="%" class="text-field" readonly>
						</fieldset>

						<!-- Selling Price -->
						<fieldset><legend data-translate="textContent" class="active-txt">selling</legend>
							<input name="selling" value="<?=$item['selling']?>" size="5" placeholder="0.00" class="text-field">
						</fieldset>
						
						<!--~~ Dumping ~~-->
						<fieldset><legend data-translate="textContent" class="green-txt">dumping</legend>
							<input name="dumping" value="<?=$item['dumping']?>" placeholder="%" class="text-field">
						</fieldset>

						<!--~~~ Label ~~~-->
						<fieldset>
							<legend class="white-txt" data-translate="textContent">label</legend>
							<input name="label" class="text-field" value="<?=$model['label']?>" data-id="<?=$model['LabelID']?>" size="8" readonly>
						</fieldset>

						<!--~ Item Name ~-->
						<fieldset><legend class="white-txt" data-translate="textContent">item</legend>
							<input name="item" placeholder="..." value="<?=$item['item']?>" class="text-field">
						</fieldset>

						<hr><hr>

						<!-- If Stock Out -->
						<fieldset><legend class="gold-txt" data-translate="textContent">stock out</legend>
							<div class="select">
								<select name="outstock" class="active-txt">
								<?foreach(["not available","under the order"] as $status):?>
									<option <?if($status==$item['outstock']):?>selected<?endif?> data-translate="textContent" value="<?=$status?>"><?=$status?></option>
								<?endforeach?>
								</select>
							</div>
						</fieldset>

						<!--~ Remainder ~-->
						<fieldset><legend class="gold-txt" data-translate="textContent">remainder</legend>
							<input name="remainder" placeholder="0" value="<?=$remainder?>" class="text-field">
						</fieldset>

						<!--~~~ Units ~~~-->
						<fieldset><legend data-translate="textContent">units</legend>
							<div class="select">
								<select name="unit" class="active-txt">
								<?foreach(["шт.","м","м²","г.","кг.","л."] as $unit):?>
									<option <?if($unit==$model['units']):?>selected<?endif?> value="<?=$unit?>"><?=$unit?></option>
								<?endforeach?>
								</select>
							</div>
						</fieldset>

						<!--~~ Currency ~~-->
						<fieldset><legend data-translate="textContent">currency</legend>
							<div class="select">
								<select name="currency" class="green-txt" autocomplete="off">
								<?foreach(["USD","EUR","UAH"] as $currency):?>
									<option <?if($currency==$model['currency']):?>selected<?endif?> value="<?=$currency?>"><?=$currency?></option>
								<?endforeach?>
								</select>
							</div>
						</fieldset>

						<input id="add-itm-btn" type="checkbox" name="AddItem" hidden>
						
						<script>
						(function(form){
							
							form.outstock.onchange=function(event){
								form.saved.checked = true;
								form.changeItem(event)
							}
							
							form.unit.onchange = 
							form.status.onchange=
							form.currency.onchange=
							form.RelCatID.onchange=
							form.CategoryID.onchange=
							form.subtemplate.onchange=function(event){
								form.saved.checked = true;
								form.changeModel(event)
							}
							
							form.named.oninput=function(event){
								form.saved.checked = true;
								clearTimeout(event.target.timeout);
								event.target.timeout = setTimeout(function(){
									form.changeModel(event);
								},2500);
							}
							form.selling.oninput=
							form.purchase.oninput=
							form.dumping.oninput=function(event){
								form.saved.checked = true;
								clearTimeout(event.target.timeout);
								event.target.timeout = setTimeout(function(){
									form.changeItem(event);
								},2500);
							}
							form.item.oninput=function(event){
								form.saved.checked = true;
								clearTimeout(event.target.timeout);
								event.target.timeout = setTimeout(function(){
									form.changeItem(event);
								},2500);
							}
							form.label.onfocus=function(){
								new Box(form.label.dataset.id, "labels/box/",function(lForm){
									form.label.value = lForm.named.value;
									form.label.dataset.id = lForm.LabelID.value;
									XHR.push({
										addressee:"/actions/showcase/st_label/<?=$model['PageID']?>/"+form.label.dataset.id,
										onsuccess:function(response){
											if(parseInt(response)){
												lForm.drop();
											}
										}
									});
								}).onopen=function(lForm){lForm.send.hidden=false};
							}
							form.discount.onfocus=function(){
								new Box(null, "showcase/discountbox/"+form.discount.dataset.id,function(lForm){
									form.discount.value = lForm.sticker.value;
									form.discount.dataset.id = lForm.DiscountID.value;
									XHR.push({
										addressee:"/actions/showcase/ch_item/<?=$item['ItemID']?>/DiscountID",
											body:utf8_to_b64(form.discount.dataset.id),
											onsuccess:function(response){
											if(parseInt(response)){
												if(parseInt(response)) lForm.drop();
											}
										}
									});
								});
							}
							form.remainder.onfocus=function(){
								new Box(null, "showcase/stockbox/<?=$item['ItemID']?>",function(lForm){
									XHR.push({
										addressee:"/actions/showcase/st_remainders/<?=$item['ItemID']?>",
										body:(function(stocks,key){
											lForm.querySelectorAll("table>tbody>tr>td").forEach(function(cell,i){
												if(i%2) stocks[key] = cell.textContent.trim();
												else key = cell.textContent.trim();
											});
											return JSON.encode(stocks);		
										})({}),
										onsuccess:function(response){
											if(parseInt(response)){
												form.remainder.value = response;
												lForm.drop()
											}
										}
									});
								});
							}
							form.reference.onchange=function(){
								XHR.push({
									addressee:"/actions/showcase/ch_model/<?=$model['PageID']?>/ItemID",
									body:utf8_to_b64(<?=$item['ItemID']?>)
								});
							}
							form.AddItem.onchange=function(){
								XHR.push({
									addressee:"/actions/showcase/ad_item/<?=$model['PageID']?>",
									onsuccess:function(response){
										if(isNaN(response)){
											alertBox(response);
										}else{
											let path = location.pathname.split("/");
												path[4] = response;
											location.pathname = path.join("/");
										}
									}
								});
							}
							form.changeModel=function(event){
								XHR.push({
									addressee:"/actions/showcase/ch_model/<?=$model['PageID']?>/"+event.target.name,
									body:utf8_to_b64(event.target.value),
									onsuccess:function(response){
										if(parseInt(response)) form.saved.checked = false;
									}
								});
							}
							form.changeItem=function(event){
								XHR.push({
									addressee:"/actions/showcase/ch_item/<?=$item['ItemID']?>/"+event.target.name,
									body:utf8_to_b64(event.target.value),
									onsuccess:function(response){
										if(parseInt(response)) form.saved.checked = false;
									}
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>

					<!--~~~~ FILTERS ~~~~~-->
					<?$filterset = $mySQL->getGroup("SELECT FilterID FROM item_vs_filters WHERE PageID = {int}", $model['PageID'])['FilterID']?>
					<div class="h-bar" data-translate="textContent">filters</div>
					<form id="model-filters" autocomplete="off">
						<?$i=0;
						$fPattern = JSON::parse($category['filterset']);
						foreach($fPattern as $setname=>$set):?>
						<fieldset><legend><?=$setname?></legend>
							<?foreach($set as $id=>$value):?>
							<label><input value="<?=$id?>" <?if(in_array($id, $filterset)):?>checked<?endif?> type="checkbox" hidden><span><?=$value?></span></label>
							<?endforeach?>
						</fieldset>
						<? $i++; endforeach?>
						<script>
						(function(form){
							form.onchange=function(event){
								XHR.push({
									addressee:"/actions/showcase/"+(event.target.checked ? "st_filter" : "dp_filter")+"/<?=$model['PageID']?>/"+event.target.value,
									onsuccess:function(response){
										if(parseInt(response)){}else event.target.checked = !event.target.checked;
									}
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
					<!--~~~~ OPTIONS ~~~~~-->
					<div class="h-bar logo-bg" data-translate="textContent">options</div>
					<table id="optionset" class="dark-txt" width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
						<colgroup><col width="28"><col><col><col width="28"></colgroup>
						<tbody>
							<?
							$options = JSON::parse($model['options']);
							if(empty($options)) $options = JSON::parse($category['optionset']);
							foreach($options as $key=>$val):?>
							<tr>
								<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
								<td contenteditable="true"><?=$key?></td>
								<td contenteditable="true"><?=$val?></td>
								<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode, OptionsSave)">✕</th>
							</tr>
							<?endforeach;
							if(empty($options)):?>
							<tr>
								<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
								<td contenteditable="true"></td>
								<td contenteditable="true"></td>
								<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode, OptionsSave)">✕</th>
							</tr>
							<?endif?>
						</tbody>
						<script>
						var t_options = document.currentScript.parentNode,
							b_options = t_options.querySelector("tbody"),
							properties = t_options.parentNode.querySelector("#properties");
						b_options.oninput=function(){
							properties.saved.checked = true;
							clearTimeout(t_options.timeout);
							t_options.timeout = setTimeout(function(){
								OptionsSave();	
							},3000);
						}
						var OptionsSave = function(){
							XHR.push({
								addressee:"/actions/showcase/sv_options/<?=$model['PageID']?>",
								body:(function(properties){
									t_options.querySelectorAll("tbody>tr>td").forEach(function(cell,i){
										if(i%2) properties[key] = cell.textContent.trim();
										else key = cell.textContent.trim();
									});
									return JSON.encode(properties);
								})({}),
								onsuccess:function(response){
									if(parseInt(response)) properties.saved.checked = false;
								}
							});
						}
						</script>
					</table>
				</div>

				<input id="code-editor-tab" name="tabs" type="radio" form="rightbar-tabs" hidden>
				<div id="code" class="tab">
					<div class="h-bar dark-btn-bg">
						<span class="tool">&#xeae4;</span> HTML
						<div class="toolbar r right">
							<label for="codefullscreen" title="screen mode" data-translate="title" class="screenmode-btn"></label>
						</div>
					</div>
					<xmp><?=gzdecode($model['description'])?></xmp>
					<script>
					var	CODE = ace.edit(document.currentScript.previousElementSibling);
					CODE.setTheme("ace/theme/twilight");
					CODE.getSession().setMode("ace/mode/html");
					CODE.setShowInvisibles(false);
					CODE.setShowPrintMargin(false);
					CODE.resize();
					CODE.session.on('change', function(event){
						if(CODE.curOp && CODE.curOp.command.name){
							html_change = true;
							setTimeout(function(){
								if(html_change) EDITOR.setContent(CODE.session.getValue());
								html_change = false;
							},1000);
						}
					});
					EDITOR.onload = function(){
						EDITOR.CODE = CODE;
						EDITOR.setContent( CODE.session.getValue() );
						EDITOR.save = saveContent
					}
					window.addEventListener("keydown",function(event){
						if((event.ctrlKey || event.metaKey) && event.keyCode==83){
							event.preventDefault();
							EDITOR.save();
						}
					});
					function saveAll(){
						var box = new Box('["light-btn-bg"]', "boxfather/savelogbox/modal");
						box.onopen = function(){
							saveContent(function(response){
								if(parseInt(response)){
									box.body.appendChild(doc.create("h3", {}, "Content - <span class='green-txt'>Saved</span>"));
								}else box.body.appendChild(doc.create("h3", {}, "Content - <span class='red-txt'>Failed save or not changes</span>"));
								
								saveMediaset(function(response){
									if(parseInt(response)){
										box.body.appendChild(doc.create("h3", {}, "Mediaset - <span class='green-txt'>Saved</span>"));
									}else box.body.appendChild(doc.create("h3", {}, "Mediaset - <span class='red-txt'>Failed save or not changes</span>"));
								});
							});
						}
					}
					function saveContent(onsave){
						XHR.push({
							addressee:"/actions/showcase/sv_description/<?=$model['PageID']?>",
							headers:{
								"Content-Type":"text/html"
							},
							body:EDITOR.getContent(),
							onsuccess:function(response){
								if(onsave) onsave(response);
							}
						});
					}
					function saveMediaset(onsave){
						XHR.push({
							addressee:"/actions/showcase/sv_images/<?=$model['PageID']?>",
							body:(function(mediaset){
								doc.querySelectorAll("#mediaset>#slideshow>form").forEach(function(form,i){
									let obj = form.querySelector("img,video");
									mediaset.push({
										url:obj.src,
										hidden:form.hidden.checked ? "NO" : "YES",
										type:obj.nodeName.toLowerCase(),
										alt:form.alt.value.trim().replace(/"/g,"”").replace("/'/g","’"),
										description:form.description.value.trim().replace(/"/g,"”").replace("/'/g","’"),
										color:form.color.value,
										position:form.position.value,
									});
								});
								return JSON.encode(mediaset);
							})([]),
							onsuccess:function(response){if(onsave) onsave(response)}
						});
					}
					</script>
				</div>

				<?elseif(CATEGORY):?>
				
				<!--~~~~ FILTERSET ~~~~-->
				<input id="filterset-tab" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
				<div id="filterset" class="tab white-bg">
					<div class="h-bar active-bg">
						<div class="toolbar r right">
							<label title="save" data-translate="title" class="tool" onclick="saveFilters()">&#xf0c7;</label>
							<label title="add filterset" data-translate="title" class="tool" onclick="addFilterset()">&#xe146;</label>
							<label title="show pattern" data-translate="title" class="tool" onclick="showPattern(filtersToJson({}), jsonTofilters)">⌘</label>
						</div>
						<span data-translate="textContent">filters</span>
					</div>
					<table width="100%" cellpadding="5" cellspacing="0" rules="cols" bordercolor="#CCC">
						<colgroup><col width="26"><col><col width="26"></colgroup>
						<?foreach($fPattern as $setname=>$set):?>
						<tbody>
							<tr class="dark-btn-bg">
								<th class="tool" onclick="this.parent(2).swap(false,1)">&#xe86b;</th>
								<td class="setname" contenteditable="true" align="center"><?=$setname?></td>
								<th class="tool" onclick="removeSet(this.parent(2))">&#xf011;</th>
							</tr>
							<?foreach($set as $id=>$value):?>
							<tr>
								<th class="tool" title="add row" data-translate="title" onclick="addFilterValue(this.parentNode)">+</th>
								<td onfocus="onCell(this)" contenteditable="true"><?=$value?></td>
								<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
							</tr>
							<?endforeach?>
						</tbody>
						<?endforeach?>
					</table>
					<datalist id="filterlist">
						<?foreach($mySQL->get("SELECT * FROM gb_filters") as $filter):?>
						<option value="<?=$filter['caption']?>"><?=$filter['caption']?></option>
						<?endforeach?>
					</datalist>
					<template id="filterset-tpl">
						<tbody>
							<tr class="dark-btn-bg">
								<th class="tool" onclick="this.parent(2).swap(false,1)">&#xe86b;</th>
								<td class="setname" contenteditable="true" align="center"></td>
								<th class="tool" onclick="removeSet(this.parent(2))">&#xf011;</th>
							</tr>
							<tr>
								<th class="tool" title="add row" data-translate="title" onclick="addFilterValue(this.parentNode)">+</th>
								<td onfocus="onCell(this)" contenteditable="true"></td>
								<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
							</tr>
						</tbody>
					</template>
					<template>
						<th class="tool" title="add row" data-translate="title" onclick="addFilterValue(this.parentNode)">+</th>
						<td onfocus="onCell(this)" contenteditable="true"></td>
						<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
					</template>
					<script>
					var subrow = document.currentScript.previousElementSibling;
					
					function onCell(cell){
						var inp = doc.create("input",{value:cell.textContent,list:"filterlist",onblur:"this.parentNode.textContent = this.value"});
						cell.textContent = "";
						cell.appendChild(inp);
						inp.focus();
					}
					function saveFilters(){
						XHR.push({
							addressee:"/actions/showcase/sv_filterset/<?=CATEGORY?>",
							body:filtersToJson({})
						});
					}
					var removeSet=(set)=>{set.parentNode.removeChild(set)};
					var addFilterValue=(row)=>{row.insertAdjacentElement("afterEnd", doc.create("tr",{}, subrow.cloneNode(true).content))}
					var addFilterset=()=>{doc.querySelector("#filterset>table").appendChild(doc.querySelector("#filterset-tpl").cloneNode(true).content)}
					
					var filtersToJson = function(filterset){
						doc.querySelectorAll("#filterset>table>tbody").forEach(function(set){
							var caption = set.querySelector("tr:first-child>td").textContent.trim();
							filterset[caption] = [];
							set.querySelectorAll("tr:not(:first-child)>td").forEach(function(cell){
								filterset[caption].push(cell.textContent.trim());
							});
						});
						return JSON.encode(filterset);
					}
					var jsonTofilters = function(json){
						var filters = doc.querySelector("#filterset>table");
							filters.querySelectorAll("tbody").forEach(function(set){
								set.parentNode.removeChild(set);
							});
						var pattern = JSON.parse(json);
						for(var key in pattern){
							var caption = doc.create("tr",{class:"dark-btn-bg"});
							caption.create("th",{class:"tool",title:"Add Row",onclick:"this.parent(2).swap(false,1)"},"&#xe86b;");
							caption.create("td",{class:"setname",contenteditable:"true",align:"center"},key);
							caption.create("th",{class:"tool",title:"Delete Row",onclick:"removeSet(this.parent(2))"},"&#xf011;");

							var set = doc.create("tbody");
								set.appendChild( caption );

							for(var i=0; i<pattern[key].length; i++){
								var row = doc.create("tr")
								row.create("th",{class:"tool",title:"Add Row",onclick:"addFilterValue(this.parentNode)"},"+");
								row.create("td",{contenteditable:"true",onfocus:"onCell(this)"},pattern[key][i]);
								row.create("th",{class:"tool",title:"Delete Row",onclick:"deleteRow(this.parentNode)"},"✕");
								
								set.appendChild(row);
							}
							filters.appendChild(set);
						}
					}
					</script>
				</div>

				<!--~~~~ OPTIONSET ~~~~-->
				<input id="options-tab" name="tabs" type="radio" form="rightbar-tabs" hidden>
				<div id="options" class="tab white-bg">
					<div class="h-bar logo-bg">
						<div class="toolbar r right">
							<label title="save" data-translate="title" class="tool" onclick="SaveOptionsTpl()">&#xf0c7;</label>
							<label title="show pattern" data-translate="title" class="tool" onclick="showPattern(optionsToJson({}), jsonTooptions)">⌘</label>
						</div>
						<span data-translate="textContent">options</span>
					</div>
					<table width="100%" cellpadding="5" cellspacing="0" rules="cols" bordercolor="#CCC">
						<colgroup><col width="26"><col><col><col width="26"></colgroup>
						<tbody>
							<?
							$options = JSON::parse($category['optionset']);
							foreach($options as $key=>$val):?>
							<tr>
								<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
								<td contenteditable="true"><?=$key?></td>
								<td contenteditable="true"><?=$val?></td>
								<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
							</tr>
							<?endforeach;
							if(empty($options)):?>
							<tr>
								<th class="tool" title="add row" data-translate="title" onclick="addRow(this.parentNode)">+</th>
								<td contenteditable="true"></td>
								<td contenteditable="true"></td>
								<th class="tool" title="delete row" data-translate="title" onclick="deleteRow(this.parentNode)">✕</th>
							</tr>
							<?endif?>
						</tbody>
					</table>
					<script>
					var SaveOptionsTpl = function(){
						XHR.push({
							addressee:"/actions/showcase/sv_optionset/<?=CATEGORY?>",
							body:optionsToJson({})
						});
					}
					var optionsToJson = function(properties,key){
						doc.querySelectorAll("#options>table>tbody>tr>td").forEach(function(cell,i){
						if(i%2) properties[key] = cell.textContent.trim();
							else key = cell.textContent.trim();
						});
						return JSON.encode(properties);
					}
					var jsonTooptions = function(json){
						var pattern = JSON.parse(json);
						var optionset = doc.create("tbody");
						for(var key in pattern){
							var row = doc.create("tr")
							row.create("th",{class:"tool",title:"Add Row",onclick:"addRow(this.parentNode)"},"+");
							row.create("td",{contenteditable:"true"},key);
							row.create("td",{contenteditable:"true"},pattern[key]);
							row.create("th",{class:"tool",title:"Delete Row",onclick:"deleteRow(this.parentNode)"},"✕");
							optionset.appendChild(row);
						}
						var tbody = doc.querySelector("#options>table>tbody");
						tbody.parentNode.replaceChild(optionset, tbody);
					}
					</script>
				</div>
				<?endif?>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<?if(PRODUCT):?>
					<label title="Metadata" class="tool" for="right-default" data-translate="title">&#xe871;</label>
					
					<div class="toolbar">
						<!--<label title="microdata" class="tool" data-translate="title" onclick="new Box('<?=MATERIAL_ID?>','sitemap/microdatabox')">&#xe8ab;</label>-->
						<label title="customizer" class="tool" data-translate="title" onclick="new Box(null,'customizer/box/<?=$model['PageID']?>')">&#xe993;</label>
					</div>
					
					<label title="code editor" class="tool" for="code-editor-tab" data-translate="title">&#xeae4;</label>

					<?elseif(CATEGORY):?>
					<div class="toolbar">
						<label title="filters" class="tool" for="filterset-tab" data-translate="title">&#xe5d1;</label>
						<label title="options" class="tool" for="options-tab" data-translate="title">&#xf05e;</label>
					</div>
					<?endif?>
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
			body.querySelector("#screenmode").checked = (STANDBY.screenmode=="true");
		})(document.currentScript.parentNode);
		<?endif?>
		var LANGUAGE = "<?=$language?>";
		function reloadTree(lang){
			XHR.push({
				addressee:"/actions/showcase/rl_tree/"+lang,
				onsuccess:function(response){
					var path = location.pathname.split(/\//);
						path[1] = "showcase";
						path[2] = lang;
					doc.querySelector("#categories>.root").outerHTML = response;
					window.history.pushState("", "tree", path.join("/"));
				}
			});
			return lang;
		}
		</script>
	</body>
</html>