/*
let options = [
	"OPERATION-NAME=findItemsByKeywords",
	"SERVICE-VERSION=1.0.0",
	"RESPONSE-DATA-FORMAT=JSON",
	"callback=response",
	"keywords=Peugeot 3008 2010",
	"paginationInput.entriesPerPage=6",
	"REST-PAYLOAD",
	"GLOBAL-ID=EBAY-UK",
	"siteid=0",
	"SECURITY-APPNAME=ViktorSi-Motordoc-PRD-b72183157-280fb647"
];
							
XHR.push({
	addressee:"https://svcs.ebay.com/services/search/FindingService/v1?"+options.join("&"),
	headers:{
		"Content-Type":"application/x-www-form-urlencoded"
	},
	onsuccess:function(response){
		form.resp.value = response;
	}
});
*/