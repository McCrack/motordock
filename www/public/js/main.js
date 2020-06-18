function refreshFilterset(form){
	var makes = [];
	form.querySelectorAll(".filterset > label > input:checked").forEach(function(inp){
		makes.push(inp.value)
	});
	XHR.push({
		method: "GET",
		addressee: "/ajax/all-makers/"+makes.join('-'),
		onsuccess: function(response){
			form.querySelector(".filterset").innerHTML = response;
		}
	});
}

function refreshCatalog(path){
	/*
	var makes = [];
	form.querySelectorAll(".filterset > label > input:checked").forEach(function(inp){
		makes.push(inp.value)
	});
	var path = location.pathname.split(/\//);
		path[2] = makes.join("-");
	window.history.pushState(null, "filters",  path.join("/"));
	*/
	path[0] = "ajax";
	XHR.push({
		method: "GET",
		addressee: "/"+path.join("/"),
		onsuccess: function(response){
			var catalog = document.querySelector(".catalog");
			catalog.innerHTML = response;
			initCatalog(catalog);
		}
	});
}

function refreshCart(){

	var cart = document.querySelector("main > aside > .cart");

	if(Object.keys(CART).length > 0){
		XHR.push({
			method: "POST",
			addressee: "/ajax/cart",
			body: JSON.encode(CART),
			onsuccess: function(response){
				cart.innerHTML = response;

				CART = {};
				cart.querySelectorAll(".snippet").forEach(function(snippet){
					CART[snippet.dataset.id] = snippet.dataset.price;
				});

				window.localStorage.cart = JSON.encode( CART );
				document.querySelectorAll(".cart-btn").forEach(function(btn){
					btn.dataset.amount = Object.keys(CART).length;
				});

				cart.querySelectorAll(".drop-btn").forEach(function(btn){
					btn.onclick = function(){
                        delete(CART[btn.dataset.id]);
                        window.localStorage.cart = JSON.encode( CART );
                        cart.removeChild(btn.parent(2));

                        document.querySelectorAll(".cart-btn").forEach(function(btn){
							btn.dataset.amount = Object.keys(CART).length;
						});
						if(!Object.keys(CART).length){
							document.querySelector("main > aside .order-btn").classList.toggle("disabled", true);
							cart.appendChild(document.create(
								'p',{class: "h2 silver-txt text-center py-4 text-tiny"},"Einkaufswagen ist leer"
							));
						}
					}
				});
			}
		});
		document.querySelector("main > aside .order-btn").classList.toggle("disabled", false);
	}
}

function addToCart(id, price){
	CART = JSON.parse( window.localStorage.cart || '{}' );
	CART[id] = parseInt(price);
	window.localStorage.cart = JSON.encode( CART );
	document.querySelectorAll(".cart-btn").forEach(function(btn){
		PopupManager.cart.checked = true;
		refreshCart();
		btn.dataset.amount = Object.keys(CART).length;
	});
}
function initCatalog(catalog){
	if(catalog) catalog.querySelectorAll(".snippet > .snippet-footer > button.toCart").forEach(function(btn){
		btn.onclick = function(){
			addToCart(btn.dataset.id, btn.dataset.price);
		}
	})
}

function showMessage(slug, onsubmit){
	XHR.push({
		method: "GET",
		addressee: "/ajax/message/"+slug,
		onsuccess: onsubmit
	});
}

function changePath(items){
	var path = window.location.pathname.split("/");
	for(var i in items){
		path[i] = items[i];
	}
	window.history.pushState(null, "filters",  path.join("/"));
	return path;
}

/*****************/

function confirmStr(value, list){
	var i = 0;
	var stack = {};
	value = value.toLowerCase();
	for(var key in list){
		if(value == key){
			stack[key] = list[key];
			break;
		}
		var match = true;
		for(var j=value.length; j--;){
			if(value[j] != key[j]){
				match = false;
				break;
			}
		}
		if(match){
			stack[key] = list[key];
			if(++i > 4){
				break;
			}
		}
	}
	return stack;
}
function buildDatalist(list, datalist, inpName){
	datalist.innerHTML = "";
	for(var key in list){
		var label = document.create('label', {}, list[key]['name']);
			label.appendChild(document.create('input', {
				'hidden': "",
				'type': "radio",
				'name': inpName,
				'value': list[key]['name']
			}));
		datalist.appendChild(label);
	}
	datalist.classList.toggle('show', true);
}
function getModels(brandId, callback){
	XHR.push({
		method: "GET",
		addressee: "/ajax/models/"+brandId,
		onsuccess: function(response){
			try{
				callback(JSON.parse(response));
			}catch(e){
				callback({});
			}
		}
	});
}