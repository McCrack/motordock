<?php
	
	$rows = $mySQL->get("SELECT LineupID,logo,Mark FROM gb_lineups GROUP BY Mark ORDER BY Mark ASC");
	$handle = "s:".time();
?>
<div id="<?=$handle?>" class="mount" style="width:860px">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
	.lineup-box>div.box-body{
		display:grid;
		grid-gap:1px;
		grid-template-rows:1.2fr .8fr;
		grid-template-columns:auto 320px 300px;
	}
	.lineup-box>div.box-body>aside{
		grid-row:1/3;
		padding:6px;
		font-size:0;
		overflow:auto;
		max-height:72vh;
		background-color:#EEE;
	}
	.lineup-box>div.box-body>aside>label{
		width:50%;
		padding:5px;
		cursor:pointer;
		font-size:14px;
		line-height:28px;
		display:inline-block;
		box-sizing:border-box;
		white-space:nowrap;
		overflow: hidden;
		text-overflow:ellipsis;
	}


	.lineup-box>div.box-body>fieldset{
		grid-column:2/3;
		margin:0;
		padding:10px;
		overflow:auto;
		border-width:0;
		max-height:80vh;
		border-top:1px solid #AAA;
	}
	.lineup-box>div.box-body>fieldset:nth-child(2){
		column-count:4;
		-moz-column-count:4;
		-webkit-column-count:4;
	}
	.lineup-box>div.box-body>fieldset:nth-child(3){
		column-count:2;
		-moz-column-count:2;
		-webkit-column-count:2;
	}
	.lineup-box>div.box-body>fieldset>label{
		font-size:15px;
		line-height:22px;
		width:100%;
		cursor:pointer;
		display:inline-block;
	}
	.lineup-box>div.box-body>aside>label:hover,
	.lineup-box>div.box-body>aside>label>input:checked+span,
	.lineup-box>div.box-body>fieldset>label:hover,
	.lineup-box>div.box-body>fieldset>label>input:checked+span{
		color:#00ADF0;
		text-decoration:underline;
		text-decoration-color:black;
	}

	.lineup-box>div.box-body>section{
		grid-area:1/3/3/4;
		
		font-size:15px;
		padding:15px 10px;
	}
	.lineup-box>div.box-body>section>div{
		display:grid;
		grid-gap:10px;
		grid-template-columns:1fr 1fr;
	}

	.lineup-box>div.box-body>section input,
	.lineup-box>div.box-body>section output{
		padding:8px;
		border-width:0;
		border-radius:3px;
		vertical-align:middle;
		box-sizing:border-box;
			max-width:140px;
	}
	.lineup-box>div.box-body>section input{
		font-size:14px;
		box-shadow:inset 0 0 5px 0 rgba(0,0,0, .5);
		background-image:linear-gradient(to top, #FFF, #EEE);	
	}
	.lineup-box>div.box-body>section output{
		margin:16px 0;
		font-size:15px;
	}
	.lineup-box>div.box-body>section output::before{
		color:#888;
		content:attr(placeholder);
	}

	.lineup-box>div.box-body>section img{
		color:#555;
		position:relative;
		object-fit:contain;
		border-radius:5px;
		border:2px solid white;
		background-color:white;
	}
	.lineup-box>div.box-body>section>div>img{
		width:100%;
		height:95px;
		line-height:95px;
	}
	.lineup-box>div.box-body>section>img{
		width:100%;
		height:160px;
		cursor:pointer;
		line-height:160px;
		display:inline-block;
	}
	.lineup-box>div.box-body>section img::after{
		color:silver;
		content:attr(alt);
		top:0;
		left:0;
		width:100%;
		height:100%;
		font-size:28px;
		font-weight:bold;
		position:absolute;
		text-align:center;
		background-color:white;
	}
	.lineup-box>div.box-body>section>img:hover::after{
		color:#00ADF0;
	}
	.lineup-box>div.box-body button{
		width:80px;
		height:26px;
		margin:0 2px;
		cursor:pointer;
		border-width:0;
		border-radius:3px;
	}
	.lineup-box>div.box-body button:not(:disabled):hover{
		color:white;
		background:#00ADF0;
	}
	.lineup-box>div.box-body button.light-btn-bg:disabled{
		cursor:default;
		background:#BBB;
	}
	.lineup-box>div.box-body button.dark-btn-bg:disabled{
		cursor:default;
		background:#777;
	}
	</style>
	<form class="box lineup-box white-bg" autocomplete="off">
		<input id="addlineup" name="addlineup" type="checkbox" hidden>
		<button type="reset" class="close-btn white-txt" title="close" data-translate="title">✕</button>
		<div class="box-caption black-bg">&#xe904;<?include_once("components/movebox.php")?></div>
		<div class="h-bar active-bg" data-translate="textContent">Lineups</div>
		<div class="box-body">
			<aside>
				<?php
				$labels = $mySQL->get("SELECT LabelID,label,logo FROM gb_labels");
				foreach($labels as $itm):?>
				<label>
					<img src="<?=$itm['logo']?>" width="28" align="left" hspace="5">
					<input type="radio" name="label" value="<?=$itm['LabelID']?>" hidden>
					<span><?=$itm['label']?></span>
				</label>
				<?endforeach?>
			</aside>
			<fieldset><legend>Lineup</legend>
				
			</fieldset>
			<fieldset><legend>Model</legend>
				
			</fieldset>
			<section class="body-bg light-txt">
				<div>
					<div>
						<div class="toolbar t right">
							<label class="tool left" title="Reset"><input name="reset_id" type="checkbox" hidden>&#xf021;</label>
						</div>
						<output name="LineID" placeholder="ID: "></output><br>
						<output name="LabelID" placeholder="LabelID: "></output><br><br>
						<input name="Mark" size="10" readonly placeholder="Label" required>
					</div>
					<img data-field="logo" alt="LOGO" align="right">
					
					<input name="Lineup" placeholder="Lineup" size="10" required>
					<input name="Model" placeholder="Model" size="10" required>
				
					<input name="challenge" readonly placeholder="Last Update">
					<div>
						<br>
						<label class="right">Published <input name="published" type="checkbox"></label>
					</div>
				</div>
				<img data-field="image" alt="VEHICLE" vspace="8">
				<p align="right">
					<button name="ct_btn" type="submit" class="light-btn-bg" disabled>Create</button>
					<button name="rm_btn" class="dark-btn-bg" disabled>Delete</button>
				</p>
			</section>
		</div>
		<template>
			<th><img src="" alt="&#xe94b;" width="32" align="left"></th>
			<!--<td></td>-->
			<td></td>
			<!--<td></td>-->
			<th class="tool drop-mark" title="delete service" data-translate="title">✕</th>
		</template>
		<script>
		(function(form){
			form.onreset=function(event){form.drop()}
			form.onchange=function(event){
				if(event.target.name=="label"){
					form.LabelID.value = event.target.value;
					form.Mark.value = event.target.parentNode.textContent.trim();
					form.querySelector("section>div>img").src = event.target.previousElementSibling.src;
					form.LineID.value =
					form.Model.value =
					form.Lineup.value = 
					form.challenge.value = "";
					form.querySelector("section>img").src = "/images/vehicle.png";
					form.published.checked = true;
					
					form.ct_btn.disabled = false;
					form.rm_btn.disabled = true;
					XHR.push({
						addressee:"/actions/challenger/gt_mark/"+event.target.value,
						onsuccess:function(response){
							form.querySelectorAll(".box-body>fieldset:nth-child(3)>label").forEach(function(model){
								model.parentNode.removeChild(model);
							});
							var lst = form.querySelector(".box-body>fieldset:nth-child(2)");
							lst.querySelectorAll("label").forEach(function(lineup){
								lineup.parentNode.removeChild(lineup);
							});
							lst.appendChild(doc.create("template",{}, response).content);
							form.align();
						}
					});
				}else if(event.target.name=="lineups"){
					form.Lineup.value = event.target.value;

					form.LineID.value =
					form.Model.value = "";
					form.ct_btn.disabled = false;
					form.rm_btn.disabled = 
					form.published.checked = true;
					XHR.push({
						addressee:"/actions/challenger/gt_models/"+form.label.value+"/"+event.target.value,
						onsuccess:function(response){
							var lst = form.querySelector(".box-body>fieldset:nth-child(3)");
							lst.querySelectorAll("label").forEach(function(model){
								model.parentNode.removeChild(model);
							});
							lst.appendChild(doc.create("template",{}, response).content);
							form.align();
						}
					});	
				}else if(event.target.name=="model"){
					form.LineID.value = event.target.dataset.id;
					form.Model.value = event.target.value;

					form.ct_btn.disabled = true;
					form.rm_btn.disabled = false;
					XHR.push({
						addressee:"/actions/challenger/gt_model/"+event.target.dataset.id,
						onsuccess:function(response){
							var model = JSON.parse(response);

							form.challenge.value = model['last_update'];
							form.published.checked = (model['published']=="Published") ? true : false;
							form.querySelector("section>img").src = model['image'];
						}
					});	
				}
			}
			form.reset_id.onchange=function(){
				form.LineID.value =
				form.Model.value = "";

				form.ct_btn.disabled = false;
				form.rm_btn.disabled = 
				form.published.checked = true;
			}
			var timeout;
			form.Lineup.oninput=
			form.Model.oninput=function(event){
				if(form.LineID.value){
					clearTimeout(timeout);
					timeout = setTimeout(function(){
						XHR.push({
							addressee:"/actions/challenger/ch_mark/"+form.LineID.value,
							body:'{"'+event.target.name+'":"'+event.target.value+'"}'
						});
					},1000);
				}
			}
			form.published.onchange=function(event){
				if(form.LineID.value) XHR.push({
					addressee:"/actions/challenger/ch_mark/"+form.LineID.value,
					body:'{"published":"'+(form.published.checked ? "Published" : "Not Published")+'"}'
				});
			}
			form.querySelector("section>img").onclick=function(event){
				new Box(null, "boxfather/imagebox",function(frm){
					event.target.src = frm.querySelector("iframe").contentWindow.getSelectedURLs();
					if(form.LineID.value) XHR.push({
						addressee:"/actions/challenger/ch_mark/"+form.LineID.value,
						body:'{"image":"'+event.target.src+'"}'
					});
				});
			}

			form.onsubmit = function(event){
				event.preventDefault();
				if(form.LineID.value){

				}else XHR.push({
					addressee:"/actions/challenger/ad_model",
					body:JSON.encode({
						LabelID:form.LabelID.value,
						Lineup:form.Lineup.value,
						Model:form.Model.value,
						published:(form.published.checked ? "Published" : "Not Published"),
						image:form.querySelector("section>img").src
					}),
					onsuccess:function(response){
						form.LineID.value = response;
						form.ct_btn.disabled = true;
					}
				});
			}


			/*~~~~*/
			form.rm_btn.onclick=function(event){
				event.preventDefault();
				if(form.LineID.value) XHR.push({
					addressee:"/actions/challenger/rm_model/"+form.LineID.value,
					onsuccess:function(response){
						form.reset();
					}
				});
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