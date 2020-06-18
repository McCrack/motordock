<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];
	
	/*
	ARG_1 - Part / Category
	ARG_2 - Label / Lineup
	*/

	$where = [];
	$limit = 40;
	$offset = isset($_GET['pg']) ? $_GET['pg'] : 1;
	
	if(defined("ARG_1")){
		if(is_numeric(ARG_1)){

			$part = $mySQL->getRow("
			SELECT * FROM gb_parts
			CROSS JOIN gb_lineups USING(LineID)
			CROSS JOIN gb_categories USING(CatID)
			WHERE PartID={int}
			LIMIT 1", ARG_1);

			if($mySQL->status['affected_rows']>0){
				define("PART_ID", $part['PartID']);
				define("PAGE_ID", $part['PageID']);
				define("CAT_ID", $part['CatID']);
				define("LINE_ID", $part['LineID']);
				define("LABEL_ID", $part['LabelID']);

				$where[] = $mySQL->parse("PageID={int}", $part['PageID']);
				$where[] = $mySQL->parse("LineID={int}", $part['LineID']);

				$label = "";
			}else{
				define("PART_ID", false);
				define("PAGE_ID", false);
				define("CAT_ID", false);
				define("LINE_ID", false);
				define("LABEL_ID", false);
			}
		}else{

			$category = $mySQL->getRow("SELECT * FROM gb_sitemap WHERE name LIKE {str} LIMIT 1", ARG_1);

			if($mySQL->status['affected_rows']>0){
				define("CAT_ID", false);
				$where[] = $mySQL->parse("PageID={int}", $category['PageID']);
			}else{
				$category = $mySQL->getRow("SELECT * FROM gb_categories WHERE category LIKE {str} LIMIT 1", ARG_1);				
				define("CAT_ID", $category['CatID']);
				$where[] = $mySQL->parse("CatID={int}", CAT_ID);
			}
			define("PART_ID", false);
			define("PAGE_ID", $category['PageID']);

			if(defined("ARG_2")){
				if(is_numeric(ARG_2)){
					$lineup = $mySQL->getRow("SELECT * FROM gb_lineups CROSS JOIN gb_labels USING(LabelID) WHERE LineID={int} LIMIT 1", ARG_2);
					$label = "/".$lineup['LineID'];
					$where[] = $mySQL->parse("LineID={int}", $lineup['LineID']);

					define("LINE_ID", $lineup['LineID']);
				}else{
					$lineup = $mySQL->getRow("SELECT * FROM gb_labels WHERE link LIKE {str} LIMIT 1", ARG_2);
					$label = "/".$lineup['link'];
					$where[] = $mySQL->parse("LabelID={int}", $lineup['LabelID']);

					define("LINE_ID", false);
				}
				define("LABEL_ID", $lineup['LabelID']);
			}else{
				$label="";
				define("LINE_ID", false);
				define("LABEL_ID", false);
			}
		}

		$feed = $mySQL->get("
		SELECT SQL_CALC_FOUND_ROWS PartID,named,gb_parts.preview FROM gb_parts
		CROSS JOIN gb_lineups USING(LineID)
		CROSS JOIN gb_categories USING(CatID)
		WHERE ".implode(" AND ", $where)."
		LIMIT {int}, {int}",
		($offset-1)*$limit, $limit);

		$count = $mySQL->getRow("SELECT FOUND_ROWS() AS cnt")['cnt'];

		$labels = $mySQL->getTree("link","idx","SELECT * FROM gb_labels");
	}else{
		define("PART_ID", false);
		define("PAGE_ID", false);
		define("CAT_ID", false);
		define("LINE_ID", false);
		define("LABEL_ID", false);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<link rel="stylesheet" type="text/css" href="/modules/parts/index.css">
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
						</div>
						<a class="root-itm light-txt" href="/parts" data-translate="textContent">parts</a>
						<div class="root">
							<?php
							$parts = $mySQL->get("SELECT * FROM gb_sitemap WHERE parent LIKE 'showcase' ORDER BY SortID ASC");
							$categories = $mySQL->getTree("category","PageID","SELECT * FROM gb_categories");
							foreach($parts as $itm):?>
							<a href="/parts/<?=($itm['name'].''.$label)?>" class="<?if($itm['name']==ARG_1):?>active-txt<?elseif($itm['published']==='Published'):?>published-txt<?endif?>">
								<?=(empty($itm['header'])?$itm['name']:$itm['header'])?>
							</a>
							<div class="root">
								<?foreach($categories[$itm['PageID']] as $cat):?>
								<a href="/parts/<?=($cat['category'].''.$label)?>" <?if($cat['category']==ARG_1):?>class="active-txt"<?endif?>><?=$cat['alias']?></a>
								<?endforeach?>
							</div>
							<?endforeach?>
						</div>
						<script>
						(function(map){
							map.onscroll=function(){STANDBY.mapScrollTop = map.scrollTop;}
							 map.scrollTop = STANDBY.mapScrollTop;
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
						bar.tabs.forEach(function(tab){ tab.onchange=function(event){
							if(event.target.id!="left-default") STANDBY.leftbar = event.target.id;
						}});
    					if(STANDBY.leftbar && bar[STANDBY.leftbar]) bar[STANDBY.leftbar].checked = true;
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar light-txt">
				<?if(defined("ARG_1")):?>
				<div class="toolbar t">
					<label title="create item" class="tool" data-translate="title" onclick="new Box(<?=(INT)LABEL_ID?>, 'parts/createbox/<?=(INT)CAT_ID?>/<?=(INT)LINE_ID?>')">&#xe89c;</label>
				</div>
				<?endif?>
				<?if(PAGE_ID && !PART_ID):?>
				<hr class="separator">
				<div class="select">
					<select class="active-txt" autocomplete="off">
						<?if(empty($lineup['LabelID'])):?><option selected disabled>Choose a Car Mark</option><?endif?>
						<?foreach($labels as $idx=>$marks):?>
						<optgroup label="<?=$idx?>">
							<?foreach($marks as $label):?>
							<option <?if($label['LabelID']==$lineup['LabelID']):?>selected<?endif?> value="<?=$label['link']?>"><?=$label['label']?></option>
							<?endforeach?>
						</optgroup>
						<?endforeach?>
						<script>
						(function(select){
							select.onchange=function(){
								let path = location.pathname.split("/");
									path[3] = select.value;
								location.pathname = path.join("/");
							}
						})(document.currentScript.parentNode)
						</script>
					</select>
				</div>
				<?if(defined("ARG_2")):?>
				<div class="select">
					<select class="active-txt" autocomplete="off">
						<?if(empty($lineup['LineID'])):?><option selected disabled>Choose a Lineup</option><?endif?>
						<?foreach($mySQL->get("SELECT * FROM gb_lineups WHERE LabelID = {int}", $lineup['LabelID']) as $lineup):?>
						<option <?if($lineup['LineID']==ARG_2):?>selected<?endif?> value="<?=$lineup['LineID']?>"><?=$lineup['Model']?></option>
						<?endforeach?>
						<script>
						(function(select){
							select.onchange=function(){
								let path = location.pathname.split("/");
									path[3] = select.value;
								location.pathname = path.join("/");
							}
						})(document.currentScript.parentNode)
						</script>
					</select>
				</div>
				<?endif; endif?>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
			</header>
			<main>
				<div id="tile">
					<?foreach($feed as $snippet):?>
					<a class="snippet" href="/parts/<?=$snippet['PartID']?>">
						<div class="preview"><img src="<?=$snippet['preview']?>" alt="&#xe906;"></div>
						<div class="header"><?=$snippet['named']?></div>
					</a>
					<?endforeach?>
				</div>
				<div class="h-bar pagination white-txt" align="right">
				<?if(($total = ceil($count/$limit))>1):
					if($offset>4):
						$j=$offset-2?>
						<a href="?pg=1">1</a> ... 
					<?else: $j=1; endif;
					for(; $j<$offset; $j++):?><a href="?pg=<?=$j?>"><?=$j?></a><?endfor?>
					<span class="active-txt"><?=$j?></span>
					<?if($j<$total):?>
						<a href="?pg=<?=(++$j)?>"><?=$j?></a>
						<?if($j<$total):?>
						<?if(($total-$j)>1):?> ... <?endif?>
						<a href="?pg=<?=$total?>"><?=$total?></a>
						<?endif?>
					<?endif;
				endif?>
				</div>
			</main>
			<section>
				<div class="tabs">
				<?if(PART_ID):?>
				<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden>
				<div id="metadata" class="tab body-bg light-txt">
					<div class="h-bar light-bg">
						ID: <span class="white-txt"><?=$part['PartID']?>-<?=$part['CatID']?></span>/<span class="gold-txt"><?=$part['RefID']?></span>
						<hr class="separator">
						<div class="toolbar t">
							<input type="checkbox" name="saved" autocomplete="off" form="properties" hidden><span title="autosave indicator" class="tool">&#xf0c7;</span>
						</div>
						<hr class="separator">
						<a href="http://silverlake.co.uk<?=$part['Reference']?>" target="_blank" class="active-txt">Reference <small>❯</small></a>
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
												addressee:"/actions/parts/sv_part/<?=PART_ID?>/preview",
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
						<img src="<?=$part['preview']?>" alt="&#xe94a;">
					</div>
					<form id="properties" autocomplete="off">
						<!--~~~~ Named ~~~~-->
						<fieldset><legend data-translate="textContent" class="published-txt">named</legend>
							<textarea name="named" placeholder="..." class="text-field"><?=$part['named']?></textarea>
						</fieldset>
						<fieldset><legend data-translate="textContent" class="published-txt">added</legend>
							<input value="<?=date('Y-m-d', $part['added'])?>" type="date" class="text-field" readonly>
							<input value="<?=date('H:i', $part['added'])?>" type="time" class="text-field" readonly>
						</fieldset>

						<fieldset><legend data-translate="textContent" class="published-txt">category</legend>
							<div class="select">
								<select name="CatID" class="active-txt">
									<?foreach($parts as $itm):?>
									<optgroup label="<?=$itm['header']?>">
										<?foreach($categories[$itm['PageID']] as $cat):?>
										<option <?if($cat['CatID']==$part['CatID']):?>selected<?endif?> value="<?=$cat['CatID']?>"><?=$cat['alias']?></option>
										<?endforeach?>
									</optgroup>
									<?endforeach?>
								</select>
							</div>
						</fieldset>

						<!-- Selling Price -->
						<fieldset><legend data-translate="textContent" class="published-txt">price</legend>
							<input name="price" value="<?=$part['price']?>" size="10" placeholder="0.00" class="text-field">
						</fieldset>

						<fieldset><legend data-translate="textContent" class="published-txt">label</legend>
							<div class="select">
								<select name="LabelID" class="active-txt">
									<?foreach($labels as $idx=>$marks):?>
									<optgroup label="<?=$idx?>">
										<?foreach($marks as $label):?>
										<option <?if($label['LabelID']==$part['LabelID']):?>selected<?endif?> value="<?=$label['LabelID']?>"><?=$label['label']?></option>
										<?endforeach?>
									</optgroup>
									<?endforeach?>
								</select>
							</div>
						</fieldset>
						<fieldset><legend data-translate="textContent" class="published-txt">lineup</legend>
							<div class="select">
								<select name="LineID" class="active-txt">
									<?foreach($mySQL->get("SELECT LineID,Model FROM gb_lineups WHERE LabelID={int}", $part['LabelID']) as $lineup):?>
									<option <?if($lineup['LineID']==$part['LineID']):?>selected<?endif?> value="<?=$lineup['LineID']?>"><?=$lineup['Model']?></option>
									<?endforeach?>
								</select>
							</div>
						</fieldset>

						<script>
						(function(form){
							form.price.oninput=
							form.named.oninput=function(event){
								form.saved.checked = true;
								clearTimeout(event.target.timeout);
								event.target.timeout = setTimeout(function(){
									XHR.push({
										addressee:"/actions/parts/sv_part/<?=PART_ID?>/"+event.target.name,
										body:utf8_to_b64(event.target.value.trim()),
										onsuccess:function(response){
											if(parseInt(response)) form.saved.checked = false;
										}
									});
								},2500);
							}
							form.CatID.onchange=
							form.LineID.onchange=function(event){
								form.saved.checked = true;
								XHR.push({
									addressee:"/actions/parts/sv_part/<?=PART_ID?>/"+event.target.name,
									body:utf8_to_b64(event.target.value),
									onsuccess:function(response){
										if(parseInt(response)) form.saved.checked = false;
									}
								});
							}
							form.LabelID.onchange=function(){
								XHR.push({
									addressee:"/actions/parts/gt_lineups/"+form.LabelID.value,
									onsuccess:function(response){
										form.LineID.innerHTML = response;
									}
								});
							}
						})(document.currentScript.parentNode)
						</script>
					</form>
					<br>
					<!--~~~~ OPTIONS ~~~~~-->
					<table id="optionset" class="dark-txt" width="100%" rules="cols" cellpadding="5" cellspacing="0" bordercolor="#CCC">
						<caption class="h-bar logo-bg" data-translate="textContent">options</caption>
						<colgroup><col width="28"><col><col><col width="28"></colgroup>
						<tbody>
							<?$options = JSON::parse($part['optionset']);
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
							},2000);
						}
						var OptionsSave = function(){
							XHR.push({
								addressee:"/actions/parts/sv_options/<?=PART_ID?>",
								body:(function(properties){
									t_options.querySelectorAll("tbody>tr>td").forEach(function(cell,i){
										if(i%2) properties[key] = utf8_to_b64(cell.textContent.trim());
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
					<!--~~~ MEDIASET ~~~~-->
					<section id="mediaset">
						<div id="slideshow">
							<?$imageset = JSON::parse($part['imageset']);
							foreach($imageset as $itm):?>
							<img src="<?=$itm?>">
							<?endforeach?>
							<script>var SLIDER = document.currentScript.parentNode</script>
						</div>

						<div id="slidelist">
							<?foreach($imageset as $itm):?>
							<div class="card">
								<label class="drop-card">✕</label>
								<div class="preview">
									<img src="<?=$itm?>" alt="&#xe94a;">
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
								MEDIASET.saveMediaset = function(onsave){
									XHR.push({
										addressee:"/actions/parts/sv_imageset/<?=PART_ID?>",
										body:(function(imageset){
											doc.querySelectorAll("#mediaset>#slideshow>img").forEach(function(img,i){
												imageset.push(img.src);
											});
											return JSON.encode(imageset);
										})([]),
										onsuccess:function(response){if(onsave) onsave(response)}
									});
								}
								MEDIASET.querySelectorAll("label").forEach(function(label,i){
									label.onclick=function(){
										SLIDER.removeChild( SLIDER.querySelectorAll("img")[i] );
										MEDIASET.removeChild( label.parentNode);
										MEDIASET.saveMediaset();
										MEDIASET.refresSlideshow();
									}
								});
							}
							MEDIASET.refresSlideshow();
							</script>
						</div>

						<form>
							<button name="previous" id="left-btn" data-dir="-1" class="transparent-bg">❰</button>
							<button name="next" id="right-btn" data-dir="1" class="transparent-bg">❱</button>
							<button name="add" id="add-btn">add</button>
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
											var slide = doc.create("img",{src:img.url,alt:""});
											SLIDER.appendChild(slide.cloneNode(true));
											let snippet = doc.create("div", {class:"card"}, "<label class='drop-card'>✕</label>");
											let preview = doc.create("div", {class:"preview"});
												preview.appendChild( slide );
											snippet.appendChild( preview );
											MEDIASET.appendChild(snippet);
										});
										MEDIASET.refresSlideshow();
										MEDIASET.saveMediaset()
										box.drop();

									});
								}

								document.body.querySelector("#screenmode").checked = (STANDBY.screenmode=="true");
							})(document.currentScript.parentNode)
							</script>
						</form>
					</section>
				</div>
				<?endif?>
				<input id="cat-tab" name="tabs" type="radio" form="rightbar-tabs" hidden>
				<form id="cats" class="tab logo-bg dark-txt" autocomplete="off">
					<div class="h-bar dark-btn-bg">
						<span data-translate="textContent">categories</span>
						<div class="toolbar right">
							<div class="select">
								<select class="active-txt" style="vertical-align:top">
									<?foreach($parts as $cat):?>
									<option <?if($cat['PageID']==PAGE_ID):?>selected<?endif?> value="<?=$cat['PageID']?>" class="black-txt"><?=$cat['header']?></option>
									<?endforeach?>
								</select>
							</div>
						</div>
					</div>
					<table width="100%" cellspacing="0" cellpadding="5" rules="cols" bordercolor="#CCC">
						<thead>
							<tr class="light-btn-bg">
								<th width="36">ID</th>
								<th>Category</th>
								<th>Alias</th>
								<th width="128"></th>
							</tr>
						</thead>
						<tbody>
							<?foreach($categories[PAGE_ID] as $category):?>
							<tr data-id="<?=$category['CatID']?>">
								<td align="center"><?=$category['CatID']?></td>
								<td><?=$category['category']?></td>
								<td contenteditable="true"><?=$category['alias']?></td>
								<td>
									<div class="select">
										<select name="part" class="black-txt" data-id="<?=$category['CatID']?>">
											<?foreach($parts as $cat):?>
											<option <?if($cat['PageID']==$category['PageID']):?>selected<?endif?> value="<?=$cat['PageID']?>"><?=$cat['header']?></option>
											<?endforeach?>
										</select>
									</div>
								</td>
							</tr>
							<?endforeach?>
						</tbody>
						<script>
						(function(body){
							body.oninput=function(event){
								if(event.target.nodeName=="TD"){
									clearTimeout(event.target.timeout);
									event.target.timeout = setTimeout(function(){
										XHR.push({
											addressee:"/actions/challenger/ch_alias/"+event.target.parentNode.dataset.id,
											body:event.target.textContent.trim()
										});
									},2000);
								}
							}
						})(document.currentScript.parentNode)	
						</script>
					</table>
					<script>
					(function(form){
						form.onchange=function(event){
							if(event.target.name){
								XHR.push({
									addressee:"/actions/challenger/ch_part/"+event.target.dataset.id+"/"+event.target.value,
								});
							}else XHR.push({
								addressee:"/actions/challenger/gt_part/"+event.target.value,
								onsuccess:function(response){
									form.querySelector("table>tbody").innerHTML = response;
								}
							});
						}
					})(document.currentScript.parentNode);
					</script>
				</form>

				<!--~~~~~~~~~~-->
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<?if(PART_ID):?>
					<label title="Metadata" class="tool" for="right-default" data-translate="title">&#xe871;</label>
					<?else:?>
					<div><br></div>
					<?endif?>
					<div>
						<label title="categories" class="tool" for="cat-tab" data-translate="title">&#xe90b;</label>
						<label title="labels" class="tool" onclick="new Box(null,'labels/box')" data-translate="title">&#xe9d3;</label>
						<label title="Lineups" class="tool" onclick="new Box(null,'challenger/lineupsbox')">&#xeae3;</label>
					</div>
					<?if(PART_ID):?>
					<label title="Remove part" data-translate="title" class="tool" onclick="removePart(<?=PART_ID?>)">&#xe9ac;</label>
					<?else:?>
					<div><br></div>
					<?endif?>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
						<?if(PART_ID):?>
						bar['right-default'].checked = true;
						<?else:?>
						bar['cat-tab'].checked = true;
						<?endif?>
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
		</div>
		<script>
		function removePart(id){
			confirmBox('remove item',function(){
				XHR.push({
					addressee:'/actions/parts/rm_part/'+id,
					onsuccess:function(response){
						if(parseInt(response)) location.pathname = "/parts/<?=$part['category']?>/<?=LINE_ID?>";
					}
				})
			})
		}
		</script>
	</body>
</html>