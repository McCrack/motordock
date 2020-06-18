<?php
	$staff = preg_split("/,\s*/", JSON::load("modules/staff/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$settings = preg_split("/,\s*/", JSON::load("modules/settings/config.init")['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
	$access = [
		"staff"=>in_array(USER_GROUP, $staff),
		"settings"=>in_array(USER_GROUP, $settings)
	];

	//$cng = new config("../".BASE_FOLDER."/config.init");

	if(isset($_GET['path']) && file_exists($_GET['path'])){
		define("OPENED", true);
	}else define("OPENED", false);

?>
<!DOCTYPE html>
<html>
	<head>
		<?include_once("components/head.php")?>
		<script src="/js/ace/src-min/ace.js" charset="utf-8"></script>
		<script src="/modules/developer/tpl/developer.js"></script>
		<script src="/xhr/wordlist/<?=USER_LANG?>?d[0]=base&d[1]=modules&d[2]=uploader" defer></script>
		<script>var LANGUAGE = "<?=USER_LANG?>"</script>
		<style>
		#handbook>div.column{
			color:#C82;
			padding:0 16px;

			column-count:3;
			-moz-column-count:3;
			-webkit-column-count:3;
			column-rule:1px solid #555;
			-moz-column-rule:1px solid #555;
			-webkit-column-rule:1px solid #555;
			column-gap:26px;
			-moz-column-gap:26px;
			-webkit-column-gap:26px;
		}
		#handbook>div.column>div{
			width:100%;
			margin:4px 0;
			font-size:16px;
			display:inline-block;
		}
		#handbook>div.column>div>small{
			color:#777;
			display:block;
			font-size:13px;
		}
		</style>
	</head>
	<body>
		<input id="screenmode" type="checkbox" autocomplete="off" <?if(OPENED):?>checked<?endif?> hidden>
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
					<input id="left-default" name="tabs" type="radio" form="leftbar-tabs" hidden>
					<div id="modules-tree" class="tab body-bg light-txt"><?include_once("components/modules.php")?></div>

					<input id="explorer-tab" name="tabs" type="radio" form="leftbar-tabs" hidden checked>
					<div id="explorer" class="tab body-bg">
						<div class="h-bar white-txt">Explorer</div>
						<form class="root light-txt" autocomplete="off">
							<?foreach(glob("../*", GLOB_ONLYDIR) as $dir): $subdomain=basename($dir)?>
							<input id="<?=$subdomain?>" type="checkbox" value="<?=$subdomain?>" hidden>
							<label for="<?=$subdomain?>" data-path="<?=$dir?>/schemes"><?=$subdomain?></label>
							<div class="root">
								<?foreach(array_filter(glob("../".$subdomain."/resources/schemes/*.json"), "is_file") as $file):?>
								<a class="file" href="?path=<?=$file?>" data-type="application" data-name="<?=pathinfo($file)['filename']?>">&#xe8ab;</a>
								<?endforeach?>
							</div>
							<?endforeach?>
							<script>
							(function(form){
								if(STANDBY.subdomain) form.querySelector("input#"+STANDBY.subdomain).checked = true;

								form.onchange=function(event){
									STANDBY.subdomain = event.target.value;
								}
							})(document.currentScript.parentNode);
							</script>
						</form>
					</div>
				</div>
				<form id="leftbar-tabs" class="v-bar l" autocomplete="off">
					<div class="toolbar">
						<label title="modules" class="tool" for="left-default" data-translate="title">⋮</label>
						<label title="explorer" class="tool" for="explorer-tab" data-translate="title">&#xe902;</label>
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
    					if(STANDBY.leftbar) bar[STANDBY.leftbar].checked = true;
					})(document.currentScript.parentNode);
					</script>
				</form>
			</aside>
			<header class="h-bar dark-btn-bg">
				<div class="toolbar t">
					<label title="create" data-translate="title" class="tool" onclick="createSchema()">&#xe89c;</label>
				</div>
				<?if(OPENED):?>
				<div class="toolbar t">
					<label title="save" data-translate="title" class="tool" onclick="saveFile()">&#xf0c7;</label>
					<label title="delete" data-translate="title" class="tool" onclick="removeSchema()">&#xe94d;</label>
				</div>
				<div class="toolbar t right">
					<label for="screenmode" class="screenmode-btn" title="screen mode" data-translate="title" class=""></label>
				</div>
				<?endif?>
				<div class="toolbar r right">
					<?if($access['settings']):?>
					<label title="settings" data-translate="title" class="tool" onclick="new Box(null, 'settings/module_settingsbox/<?=SECTION?>');">&#xf013;</label>
					<?endif?>
				</div>
			</header>
			<?if(OPENED):?>
			<main><?include_once($_GET['path'])?></main>
			<script>
			var editor = ace.edit(document.currentScript.previousElementSibling);
			editor.setTheme("ace/theme/solarized_dark");
			editor.getSession().setMode("ace/mode/json");

			editor.setShowInvisibles(false);
			editor.setShowPrintMargin(false);
			editor.resize();
			var noChanged = true;
			editor.session.on('change', function(event){
				if(noChanged && editor.curOp && editor.curOp.command.name){
					noChanged = false;
				}
			});
			window.addEventListener("keydown",function(event){
				if((event.ctrlKey || event.metaKey) && event.keyCode==83){
					event.preventDefault();
					saveFile();
				}
			});
			function saveFile(){
				XHR.push({
					addressee:"/actions/developer/sv_file"+location.search,
					body:editor.session.getValue(),
					onsuccess:function(response){
						if(isNaN(response)){
							alertBox(response,["logo-bg","large-txt"]);
						}else noChanged = true;
					}
				});
			}
			</script>
			<?else:?>
			<main></main>
			<?endif?>

			<!--~~~~~~~~~~~~~~-->

			<?if(OPENED):?>
			<section>
				<div class="tabs">
					<input id="right-default" name="tabs" type="radio" form="rightbar-tabs" hidden checked>
					<div id="handbook" class="tab body-bg light-txt" autocomplete="off">
						<div class="h-bar">
							Handbook
							<a class="tool right light-txt" href="/microdata" title="Close Pattern">✕</a>
						</div>
						<br>
						<div class="column">
							<div class="red-txt">
								gl:name
							</div>
							<div class="red-txt">
								gl:url
							</div>
							<div class="red-txt">
								gl:logo
							</div>

							<div class="gold-txt">
								pg:type
							</div>
							<div class="gold-txt">
								pg:created_at <small class="grey-txt">(ISO_8601)</small>
							</div>
							<div class="gold-txt">
								pg:updated_at <small class="grey-txt">(ISO_8601)</small>
							</div>

							<div>
								pg:title
							</div>
							<div>
								pg:description
							</div>
							<div>
								pg:context
							</div>

							<div class="green-txt">
								pg:id
							</div>
							<div class="green-txt">
								pg:header
							</div>
							<div class="green-txt">
								pg:slug
							</div>
							<div class="green-txt">
								pg:parent_slug
							</div>
							<div class="green-txt">
								pg:language
							</div>
							<div class="green-txt">
								pg:preview
							</div>
						</div>
					</div>
				</div>
				<form id="rightbar-tabs" class="v-bar r v-bar-bg" data-default="right-default" autocomplete="off">
					<label title="Handbook" class="tool" for="right-default">&#xe871;</label>
					<script>
					(function(bar){
						bar.onsubmit=function(event){ event.preventDefault(); }
					})(document.currentScript.parentNode);
					</script>
				</form>
			</section>
			<?endif?>

			<!--~~~~~~~~~~~~~~-->
		</div>
		<script>
		function createSchema(){
			var box = new Box(null, "microdata/createbox", function(form){
				var schema = form.schema.value.trim().translite();
				XHR.push({
					addressee:"/actions/developer/ct_file",
					body:"../"+form.subdomain.value+"/resources/schemes/"+schema+".json",
					onsuccess:function(response){
						location.search="path=../"+form.subdomain.value+"/resources/schemes/"+schema+".json"
					}
				});
			});
		}
		function removeSchema(){
			confirmBox("delete schema", function(){
				XHR.push({
					addressee:"/actions/developer/remove",
					body:parse_url(location.search)['search']['path'],
					onsuccess:function(response){
						location.href = "/microdata";
					}
				});
			});
		}
		</script>
	</body>
</html>