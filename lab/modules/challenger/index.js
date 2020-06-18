function getQuery(root){
	var q = [];
	for(var key in QUERY) q.push(key+"="+QUERY[key]);
	return root+"?"+q.join("&");
}
function traversal(){
	PAGENATION = [];
	CNT.Records = CNT.Pages= 0;
	var lineup = LINEUPS.shift();
	if(lineup){
		CNG.LineID = lineup.dataset.id;
		log.caption(lineup.value, "o");
		XHR.push({
			addressee:"/actions/challenger/gt_parts/"+lineup.dataset.id,
			onsuccess:function(response){
				
				try{
					ADDED = JSON.parse(response);
					ITEMS = ADDED.RefID.slice(0);
				}catch(e){
					ITEMS = ADDED = [];
					log.caption("Parts list is Empty", "r")
				}
				
				QUERY['code'] = "mark";
				QUERY['make'] = CNG.MarkID;
				QUERY['model'] = lineup.dataset.lineup;
				QUERY['mark'] = lineup.value;
				QUERY['pgrq'] = 0;

				var query = getQuery("https://www.silverlake.co.uk/includes/ajax/vppr");
				XHR.push({
					addressee:"/actions/challenger/gt_fragment",
					body:query,
					onsuccess:function(response){

						var body = doc.querySelector("#wrapper>main>table>tbody");
						var tpl = doc.create("template",{},response);
						var pagination = doc.create("div",{class:"pagination"});
						let pages = tpl.content.querySelectorAll("#feat_part_list_nav>div.pages>ul.pn_ul>li.jump>select>option:not(:first-child)");
						if(pages.length){
							QUERY['code'] = "pg";
							pages.forEach(function(itm,i){
								QUERY['pgrq'] = itm.value;
								PAGENATION.push(getQuery("https://www.silverlake.co.uk/includes/ajax/vppr"));
							});
						}else PAGENATION.push(query);

						LOG.create("br");
						log.record("Number of pages", String(PAGENATION.length), "g");

						challenge();
					}
				});
				lineup.checked = false;
			}
		});
	}else{
		log.caption("Import Completed", "u");
		onImportCompete();
	}
}
function challenge(){
	if(CNG.Stop){
		log.caption("Stopped by User","r");
		log.record("Records Added","["+CNT.Records+"]","g");
		log.record("Pages Passed","["+CNT.Pages+"]","g");
		onImportCompete();
		return false;
	}
	let query = PAGENATION.pop();
	if(query){
		log.caption((PAGENATION.length+1)+" Pages Left");
		log.action(query);
		XHR.push({
			addressee:"/actions/challenger/gt_fragment",
			body:query,
			onsuccess:function(response){
				try{
					tpl = doc.create("template",{},response);
					buildProdList(tpl.content.querySelectorAll("table>tbody>tr:not(:first-child)"));
					extended();

				}catch(e){log.caption(e.name+": "+e.message, "r") }
			}
		});
	}else if(ADDED.RefID && ADDED.RefID.length){
		log.caption("Base Cleaning", "g");
		base_cleaning();
	}else{
		log.caption("Lineup Completed", "g");
		log.record("Records Added","["+CNT.Records+"]","g");
		log.record("Pages Passed","["+CNT.Pages+"]","g");
		LOG.create("br");
		log.reset(CNG.Respite, "Prolonged Respite", traversal);
		onImportCompete();
	}
}
function buildProdList(lst){
	THINGS = [];
	lst.forEach(function(row){
		let lnk = row.querySelector("td:nth-child(3)>a:first-child");
		if(lnk){
			if(lnk.parentNode.classList.contains("s_1")){
				/* FULL SERVICE */
				log.record(lnk.textContent, "Is Full Service","r");
			}else{
				let RefID = lnk.pathname.split("/")[5];
				if(ITEMS.inArray(RefID)){
					log.record(lnk.textContent, "Allready Existst","r");
					
					if(ADDED.RefID & ADDED.RefID.length){
						let inx = ADDED.RefID.indexOf(RefID);
						ADDED.PartID.splice(inx, 1);
						ADDED.RefID.splice(inx, 1);
						ADDED.Reference.splice(inx, 1);
					}
				}else{
					ITEMS.push(RefID);

					THINGS.push({
						RefID:RefID,
						LineID:CNG.LineID,
						named:lnk.textContent,
						Reference:"https://www.silverlake.co.uk"+lnk.pathname,
						preview:(row.querySelector("td:first-child>img") || {dataset:{src:""}}).dataset.src,
						state:row.querySelector("td:nth-child(4)").textContent,
						price:row.querySelector("td:nth-child(5)").textContent.replace(/^\D*/, "") || "0.00"
					});
				}
			}
		}
	});
	log.record("Things on List", "["+THINGS.length+"]","u");
}
function extended(){
	if(CNG.Stop){
		log.caption("Stopped by User","r");
		log.record("Records Added","["+CNT.Records+"]","g");
		log.record("Pages Passed","["+CNT.Pages+"]","g");
		onImportCompete();
		return false;
	}
	let thing = THINGS.pop();
	if(thing){
		log.action(thing['named']);
		XHR.push({
			addressee:"/actions/challenger/gt_fragment",
			body:thing['Reference'],
			onsuccess:function(response){
				let tpl = doc.create("template",{},response);
				let popup = tpl.content.querySelector("#popup_parts_container");

				thing['named'] = popup.querySelector(".popup_title").textContent;

				thing['category'] = (function(elem){
					return (elem.nodeType==3) ? elem.nodeValue.trim() : "n/a";
				})(popup.querySelector(".popup_desc").firstChild);

				thing['imageset'] = (function(imageset){
					popup.querySelectorAll(".popup_thumbs>.popup_thumb>img").forEach(function(img){
						imageset.push(img.src);
					});
					return imageset;
				})([]);

				thing['optionset'] = (function(optionset){
					popup.querySelectorAll(".popup_desc>table>tbody>tr").forEach(function(row){
						let key = row.querySelector("th:first-child,td:first-child")
						let val = row.querySelector("th:last-child,td:last-child")
						optionset[key.textContent.trim()] = utf8_to_b64(val.textContent.trim());
					});
					return optionset;
				})({ State:utf8_to_b64(thing['state']) })

				XHR.push({
					addressee:"/actions/challenger/ad_part",
					body:JSON.encode(thing),
					onsuccess:function(response){
						if(response=="Added"){
							CNT.Records++;
							log.record(thing['named'], response, "g");
						}else log.record(thing['named'], response, "r");

						log.reset(CNG['Short Respite'], "Short Respite", extended);
					}
				});
			}
		});
	}else{
		CNT.Pages++;
		log.reset(CNG.Respite, "Prolonged Respite", challenge);
	}
}
function base_cleaning(){
	let RefID = ADDED.RefID.pop();
	if(RefID){
		XHR.push({
			addressee:"/actions/challenger/gt_fragment",
			body:ADDED.Reference.pop(),
			onsuccess:function(response){
				try{
					let tpl = doc.create("template",{},response);
					let header = tpl.content.querySelector("#popup_parts_container>h1");
					if(header && header.textContent=="Oops, that part has just been removed"){
						XHR.push({
							addressee:"/actions/challenger/rm_part/"+ADDED.PartID.pop(),
							onsuccess:function(response){
								log.record("Remove Item", "#"+RefID);
								base_cleaning();
							}
						});
					}else base_cleaning();
				}catch(e){log.caption(e.name+": "+e.message, "r") }
			}
		});
		//---!!!---//
	}else{
		log.caption("Lineup Completed", "g");
		log.record("Records Added","["+CNT.Records+"]","g");
		log.record("Pages Passed","["+CNT.Pages+"]","g");
		LOG.create("br");
		log.reset(CNG.Respite, "Prolonged Respite", traversal);
		onImportCompete();
	}
}
function onImportCompete(){
	TOOLBAR.removeChild( TOOLBAR.querySelector("label[title='Abort Import']") );
}

var log = new function(){
	this.record=function(key,value,cls){
		key = key || "#";
		value = value || "";
		color = cls || "b";
		let record = doc.create("div",{}, key+": "+("<span class='"+cls+"'>"+value+"</span>").padStart(160-key.length, "."));
		LOG.appendChild(record);
		record.scrollIntoView(false);
	}
	this.caption=function(str, cls){
		cls = cls || "b";
		let record = doc.create("h3",{class:cls},str);
		LOG.appendChild(record);
		record.scrollIntoView(false);
	}
	this.button=function(cpt, action, cls){
		cls = cls || "b";
		let btn = doc.create("button",{class:cls},cpt);
		btn.onclick=function(){action()}
		LOG.appendChild(btn);
		btn.scrollIntoView(false);
	}
	this.interface=function(content, btn, action){
		let frm = doc.create("form",{});
		frm.appendChild(content);
		frm.create("button", {type:"submit"}, btn);
		frm.onsubmit=function(event){
			event.preventDefault();
			action(frm);
		}
		LOG.appendChild(frm);
		frm.scrollIntoView(false);
	}
	this.action=function(value){
		SUBLOG.innerHTML = "";
		SUBLOG.create("small",{},value);
	}
	this.reset=function(dur, cpt, oncomplete){
		cpt = cpt || "Waiting";
		let record = doc.create("div",{
			"class":"reset",
			"title":dur
		},cpt);

		SUBLOG.innerHTML = "";
		SUBLOG.appendChild(record);

		var rst = new Interval(function(i){
			record.title = i;
		},dur,1000);

		rst.oncomplete=function(){
			record.title = 0;
			SUBLOG.innerHTML = "";
			//SUBLOG.removeChild(record);
			oncomplete();
		}
	}
}