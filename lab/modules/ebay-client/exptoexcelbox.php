<?php
$now = time();
$handle = "b:".$now;
?>
<div id="<?=$handle?>" class="mount">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
    .export-box{
      width:564px;
    }
    .export-box>div.box-caption{
      font-size:15px;
    }
	.export-box>div.box-body{
		font-size:14px;
		padding:10px 20px;
	}
	.export-box>div.box-body fieldset{
		margin:10px 0;
		border-radius:5px;
		border:1px solid #AAA;
    }
	.export-box>div.box-body fieldset>legend{
		padding:0 10px;
		display:inline-block;
	}
	.export-box>div.box-body input[type='text'],
	.export-box>div.box-body>fieldset input[type='date'],
	.export-box>div.box-body>fieldset input[type='number']{
		margin:4px;
		padding:6px;
		height:28px;
		border-radius:3px;
		border:1px solid #AAA;
		box-sizing:border-box;
	}
	.export-box>div.box-body>fieldset input[type='number']{
		width:80px;
	}
	.export-box>div.box-body input[type='date']:disabled,
	.export-box>div.box-body input[type='number']:disabled{
		color:#AAA;
		background-color:#DDD;
	}
	.export-box>div.box-body input[type='text'],
	.export-box>div.box-body input[type='date']:not(:disabled),
	.export-box>div.box-body input[type='number']:not(:disabled){
		box-shadow:inset 0 4px 15px -5px rgba(0,0,0, .3);
		background-image:linear-gradient(to top, #FFF, #EEE);
	}
	.export-box>div.box-body select:disabled{
		color:#AAA;
	}
	.export-box>div.box-body select:not(:disabled){
		color:#333;
	}
	.export-box>div.box-body>section.search-result{
		overflow:auto;
		margin:0 -20px;
		padding:0 25px;
		max-height:50vh;
		box-shadow:inset 0 5px 8px -5px rgba(0,0,0, .4);
	}
	</style>
	<form class="box export-box light-btn-bg">
		<button type="reset" class="close-btn dark-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption active-bg">
			<span data-translate="textContent">&#xe925;</span>
			<? (include_once "components/movebox.php") ?>
		</div>
		<div class="h-bar light-btn-bg">Export to Excell File</div>
		<div class="box-body">
			<fieldset><legend><b>Period</b></legend>
				<div class="columns-2">
					<label><input type="radio" name="period" value="lastday" checked> <span>Last 24h</span></label>
					<label><input type="radio" name="period" value="today"> <span>Today (GMT)</span></label>
					<label><input type="radio" name="period" value="custom"> <span>Custom Period (GMT)</span></label>
					<div align="right">
						From: <input type="date" name="from" required disabled><br>
						To: <input type="date" name="to" required disabled>
					</div>
				</div>
			</fieldset>
			<fieldset><legend><b><label><input type="checkbox" name="sellerActive"> <span>Seller</span></label></b></legend>
				<div class="select" title="Store">
					<select name="SellerID" disabled>
						<?foreach($mySQL->get("SELECT SellerID,StoreName FROM cb_sellers") as $seller):?>
						<option value="<?=$seller['SellerID']?>"><?=$seller['StoreName']?></option>
						<?endforeach?>
					</select>
				</div>
			</fieldset>
			<fieldset><legend><b><label><input type="checkbox" name="pricelimits"> <span>Limits of Price</span></label></b></legend>
				<div class="columns-2">
					<div align="center">
						<span>Min Price:</span><br>
						<input name="MinPrice" type="number" min="1" value="10" size="15" disabled>
					</div>
					<div align="center">
						<span>Max Price:</span><br>
						<input name="MaxPrice" type="number" min="10" value="100" size="15" disabled>
					</div>
				</div>
			</fieldset>
			<section class="search-result white-bg">

			</section>
		</div>
		<div class="box-footer" align="center">
			<button type="submit" name="find" class="light-btn-bg">Find</button>
			<button type="reset" name="rst" class="light-btn-bg" disabled>Reset</button>
		</div>
		<script>
		(function(form){
			var query = {
				categories:[],
				period:[]
			}
			form.onreset=function(){ form.drop(); }
			var Unload = function(event){
				event.preventDefault();
				query['period']=(function(period){
					switch(form.period.value){
						case "custom":
							period.push((form.from.valueAsNumber/1000)>>0);
							period.push((form.to.valueAsNumber/1000)>>0);
							break;
						case "totday":
							period = ["<?=mktime(0,0,0)?>", "<?=$now?>"];
							break;
						default:
							period = ["<?=($now-86400)?>", "<?=$now?>"];
							break;
					}
					return period;
				})([])
				if(form.sellerActive.checked){
					query['SellerID'] = form.SellerID.value;
				}else delete(query['SellerID']);
				if(form.pricelimits.checked){
					query['MinPrice'] = form.MinPrice.value;
					query['MaxPrice'] = form.MaxPrice.value;
				}else{
					delete(query['MinPrice']);
					delete(query['MaxPrice']);
				}
				XHR.push({
					addressee:"/ebay-client/unload",
					body:JSON.encode(query),
					onsuccess:function(response){
						form.querySelector("section.search-result").innerHTML = response;
						form.align();
						form.find.name = "export";
						form.export.textContent = "Export";
						form.export.className = "dark-btn-bg";
						form.rst.disabled = false;
						form.onsubmit=Export;
					}
				});
			}
			var Export = function(event){
				event.preventDefault();
				query['header'] = form.header.value.trim();
				query['endfile'] = form.endfile.value.trim();
				query['categories'] = [];
				form.querySelectorAll("section.search-result input[name='category']:checked").forEach(function(inp){
					query['categories'].push(inp.value);
				});
				query['fields'] = [];
				form.querySelectorAll("section.search-result input[name='field']:checked").forEach(function(inp){
					query['fields'].push(inp.value);
				});
				XHR.push({
					addressee:"/ebay-client/export",
					body:JSON.encode(query),
					onsuccess:function(response){
						setTimeout(function(){
							//location.pathname = response;
						},1000);
					}
				});
			}
			form.onsubmit=Unload;
			form.sellerActive.onchange=function(){
				form.SellerID.disabled = !form.sellerActive.checked;
			}
			form.pricelimits.onchange=function(){
				form.MinPrice.disabled =
				form.MaxPrice.disabled = !form.pricelimits.checked;
			}
			form.period.forEach(function(inp){
				inp.onchange=function(){
					form.to.disabled =
					form.from.disabled = (form.period.value!="custom");
				}
			});
			form.rst.onclick=function(event){
				event.preventDefault();
				form.onsubmit=Unload;
				form.export.name = "find";
				form.find.textContent = "Find";
				form.find.className = "light-btn-bg";
				form.rst.disabled = true;
				form.querySelector("section.search-result").innerHTML = "";
				form.align();
			}
		})(document.currentScript.parentNode);
		</script>
	</form>
	<script>
	(function(mount){
		location.hash = "<?=$handle?>";
		translate.fragment(mount);
		if(mount.offsetHeight>(screen.height - 40)){
			mount.style.top = "20px";
		}else mount.style.top = "calc(50% - "+(mount.offsetHeight/2)+"px)";
	})(document.currentScript.parentNode);
	</script>
</div>
