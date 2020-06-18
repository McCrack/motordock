
var doc = document;

/* Ajax ************************************************************************/

if(window.XMLHttpRequest){
    XMLHttpRequest.prototype.ready = true;
    XMLHttpRequest.prototype.stack = [];
    XMLHttpRequest.prototype.defaults = {
        "body":'{}',
        "async":true,
        "method":"POST",
        "timeout":15000,
        "Cache-Control":"no-cache",
        "onsuccess":function(response){ return true },
        "onerror":function(response){ console.log(response); },
        "Content-Type":"application/json"   //  "text/plain", "text/xml", "text/html", "application/octet-stream", "multipart/form-data", "application/x-www-form-urlencoded";
    };
    XMLHttpRequest.prototype.push = function(request){
        if(request['addressee']){
            for(var key in XHR.defaults){
                if(XHR.defaults.hasOwnProperty(key)){
                    request[key] = request[key] || XHR.defaults[key];
                }
            }
            XHR.stack.push(request);
            XHR.execute();
        }else console.log("XHR ERROR: Not specified addressee");
    };
    XMLHttpRequest.prototype.execute = function(){
        if(XHR.ready){
            var request = XHR.stack.shift();
            XHR.ready=false;

            XHR.open(request.method, request.addressee, request.async);
            XHR.timeout = request.timeout;
            XHR.setRequestHeader("Content-Type", request['Content-Type']);
            XHR.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').content);

            var indicator = doc.create("div", {id:"loading-indicator", style:"opacity:1.0"});
            doc.body.appendChild(indicator);
            XHR.onreadystatechange=function(){
                if(XHR.readyState==4){
                    XHR.ready=true;
                    doc.body.removeChild(indicator);
                    (XHR.status==200) ? request.onsuccess(XHR.response) : request.onerror(XHR.statusText);
                    if(XHR.stack.length) XHR.execute();
                }
            }
            XHR.send(request.body);
        }
    }
    var XHR = new XMLHttpRequest();
}

/* Object **********************************************************************/

Object.defineProperty(Object.prototype, "length", {
    get:function(){
        var length = 0;
        for(var i in this){
            if(this.hasOwnProperty(i)) length++;
        }
        return length;
    }
});

/* Array ***********************************************************************/

Array.prototype.inArray = function(value){
    for(var i=this.length; i--;) if(this[i] == value) return i;
    return NaN;
}

/* HTMLDocument ****************************************************************/

    HTMLDocument = Document || HTMLDocument;

    Object.defineProperty(HTMLDocument.prototype, "width", {
        get:function(){
            return self.innerWidth || document.documentElement.clientWidth;
        }
    });
    Object.defineProperty(HTMLDocument.prototype, "height", {
        get:function(){
            return self.innerHeight || document.documentElement.clientHeight;
        }
    });
    Object.defineProperty(HTMLDocument.prototype, "currentScrollTop", {
        get:function(){
            return  window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
        }
    });
    Object.defineProperty(HTMLDocument.prototype, "currentScrollLeft", {
        get:function(){
            return  window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft;
        }
    });

    HTMLDocument.prototype.create=function(tagName, attributes){
        var obj = this.createElement(tagName);
        for(var i=2; i<arguments.length; i++){
            if(typeof(arguments[i])=="string"){
                obj.innerHTML=arguments[i];
            }else if(typeof(arguments[i])==="object"){
                if(arguments[i].nodeType in {"1":null, "3":null, "11":null}){
                    obj.appendChild(arguments[i]);
                }else if(arguments[i].constructor == Array){
                    obj.appendChilds(arguments[i]);
                }
            }
        }
        for(var key in attributes){
            if(attributes.hasOwnProperty(key)){
                obj.setAttribute(key, attributes[key]);
            }
        }
        return obj;
    }
    HTMLDocument.prototype.fragment=function(content){
        if(content){
            if(typeof(content)=="string"){
                var temp = document.createElement("template");
                    temp.innerHTML = content;
                var obj = temp.content;
            }else if(typeof(content)=="object" && content.nodeType in {1:null, 3:null, 11:null}){
                var obj = this.createDocumentFragment();
                obj.appendChilds(content);
            }
        }return obj;
    }


/* HTMLFragment / HTMLElement **************************************************/

    DocumentFragment.prototype.first=HTMLElement.prototype.first=function(tagName){
        tagName = tagName || "";
        tagName = tagName.toUpperCase();
        var node = this.firstElementChild;
        if(tagName){
            while(node && node.nodeName!=tagName){
                node = node.nextElementSibling;
            }
        }
        return node || null;
    }
    DocumentFragment.prototype.last=HTMLElement.prototype.last=function(tagName){
        tagName = tagName || "";
        tagName = tagName.toUpperCase();
        var node = this.lastElementChild;
        if(tagName){
            while(node && node.nodeName!=tagName){
                node = node.previousElementSibling;
            }
        }
        return node || null;
    }
    DocumentFragment.prototype.appendChildren = HTMLElement.prototype.appendChildren = function(nodeList){
        for(var i=0; i<nodeList.length; i++){
            this.appendChild(nodeList[i]);
        }
        return this.children;
    }

/* HTMLElement *****************************************************************/

    HTMLElement.prototype.parent=function(level){
        switch( typeof(level) ){
            case "string":
                level = level.toUpperCase();
                var node = this.parentNode;
                while(node && node.nodeName != level){
                    node = node.parentNode;
                }
            break;
            case "number":
                level=level || 1;
                var node = this;
                for(; level--;){
                    if(node){ node = node.parentNode; }
                }
            break;
            default:
                var node = this.parentNode;
            break;
        }
        return node;
    }
    HTMLElement.prototype.insertToBegin=function(node){
        if(node){
            var first;
            if(first = this.firstChild){
                first = this.insertBefore(node, first);
            }else first = this.appendChild(node);
            return first;
        }else return false;
    }
    HTMLElement.prototype.insertBeforeNode=function(node){
        if(typeof node==="string"){
            this.insertAdjacentHTML("afterBegin", node);
        }else if(typeof node==="object"){
            this.insertAdjacentElement("afterBegin", node)
        }else return false;
    }
    HTMLElement.prototype.insertAfter=function(node){
        if(typeof node==="string"){
            this.insertAdjacentHTML("afterEnd", node);
        }else if(typeof node==="object"){
            this.insertAdjacentElement("afterEnd", node)
        }else return false;
    }
    HTMLElement.prototype.build=function(list, clean){
        if(clean) this.innerHTML = "";
        var fragment = document.build(list);
        this.appendChild(fragment);
        return fragment;
    }
    HTMLElement.prototype.getCss=function(rule, pseudo){
        pseudo = pseudo || "";
        var obj = window.getComputedStyle(this, "");
        return obj.getPropertyValue(rule);
    }
    Object.defineProperty(HTMLElement.prototype, "fullScrollTop", {
        get:function(){
            var srl = 0;
            var obj = this;
            while(obj.nodeType==1){
                srl += obj.scrollTop;
                obj = obj.parentNode;
            }
            return srl;
        }
    });
    Object.defineProperty(HTMLElement.prototype, "fullScrollLeft", {
        get:function(){
            var srl = 0;
            var obj = this;
            while(obj.nodeType==1){
                srl += obj.scrollLeft;
                obj = obj.parentNode;
            }
            return srl;
        }
    });

    HTMLImageElement.prototype.reload=function(){
        var path = this.src.split(/#/);
            path[1] = new Date().getTime();
        this.src=path.join("#");
    }

/* DATE ************************************************************************/

    Date.prototype.daysInMonth = function() {
        return 33 - new Date(this.getFullYear(), this.getMonth(), 33).getDate();
    };

/* NodeList ********************************************************************/

    NodeList.prototype.on = function(e, handler){
        for(var i=this.length; i--;){
            this[i].addEventListener(e, handler);
        }
    }
    NodeList.prototype.set = function(property, value){
        for(var i=this.length; i--;){
            this[i][property] = value;
        }
    }

/* JSON ************************************************************************/

JSON.encode=function(obj, level){
    level = level || 0;
    var t = typeof(obj);
    if(typeof obj!="object"){
        return '"'+String(obj)+'"';
    }else{
        var t="",
            json = [],
            isArray = (obj && obj.constructor == Array);
        for(var i=0; i<level; i++) t += '\t';
        for(var key in obj){
            if(obj.hasOwnProperty(key)){
                if(typeof obj[key]==="object"){
                    var item = JSON.encode(obj[key], level+1);
                }else var item = '"'+String(obj[key]).trim()+'"';
                json.push( (isArray ? '' : '"'+key.replace(/"/g,"&quot;").trim()+'":')+item );
            }
        }
        return isArray ? '[\n\t'+t+json.join(',\n\t'+t)+'\n'+t+']' : '{\n\t'+t+json.join(',\n\t'+t)+'\n'+t+'}';
    }
};

/* COOKIES *********************************************************************/

function getCookie(key){
    var cookies=document.cookie.split(/;|=/);
    for(var i=0; i<cookies.length; i++){
        if(cookies[i].trim()==key){
            return decodeURI(cookies[++i]);
        }
    }
}
function setCookie(name, value, props) {
    props = props || {}
    var exp = props.expires
    if (typeof exp == "number" && exp) {
        var d = new Date()
        d.setTime(d.getTime() + exp*1000)
        exp = props.expires = d
    }

    if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }
    value = encodeURIComponent(value)
    var updatedCookie = name + "=" + value
    for(var propName in props){
        updatedCookie += "; " + propName
        var propValue = props[propName]
        if(propValue !== true){ updatedCookie += "=" + propValue }
    }
    document.cookie = updatedCookie

}
function removeCookie(key){
    setCookie(key, "", {"expires":-1});
}

/* Session *********************************************************************/

var session = window.sessionStorage || new function(){
    try{
        JSON.parse(window.name);
    }catch(e){ window.name = "{}"; }

    this.getItem = function(varName){
        return JSON.parse(window.name)[varName] || null;
    }
    this.setItem = function(varName, val){
        var temp=JSON.parse(window.name);
            temp[varName]=val;
            window.name=JSON.stringify(temp);
    }
}

var storage = window.localStorage || session;

session.__proto__.open = function(){
    var today = new Date();
        today.setUTCHours(0,0,0,0);
    var oldTimestamp = session.getItem("today");
    var newTimestamp = today.getTime();
    if(newTimestamp > oldTimestamp){
        session.setItem("today", today.getTime());
        return false;
    }else return true;
}

/* Date ************************************************************************/

function date(pattern, timestamp){
    var M = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    var F = ["January","February","March","April","May","June","July","August","September","October","November","December"];
    pattern = pattern||"d.m.Y";
    var today = timestamp ? new Date(timestamp) : new Date();
    var params = pattern.trim().split(/\W+/);
    var set={
        "d":"%02d".format([today.getDate()]),
        "m":"%02d".format([today.getMonth()+1]),
        "M":M[today.getMonth()],
        "F":F[today.getMonth()],
        "Y":"%04d".format([today.getFullYear()]),
        "H":"%02d".format([today.getHours()]),
        "i":"%02d".format([today.getMinutes()]),
        "s":"%02d".format([today.getSeconds()]),
        "D":today.getDay(),
        "U":((today.getTime()/1000)^0)
    }
    for(var i=0; i<params.length; i++){
        pattern=pattern.replace(params[i], set[params[i]]);
    }
    return pattern;
}

/* Other ***********************************************************************/

var Scroller = function(){
    var scroller = this;
    this.top = 0;
    this.direction = 1;
    this.stack = [];
    this.outStack = [];
    window.addEventListener("scroll", function(event){
        scroller.direction = (document.currentScrollTop > scroller.top) ? 1 : -1;
        scroller.top = document.currentScrollTop;
        scroller.action();
    });

    this.isInRange = function(obj, offset){
        var y = obj.getBoundingClientRect().top;
        return ((y + offset) < window.innerHeight) 
            ? (y > 0)
            :false;
    }
    this.push = function(obj, offset, inRange, outRange){
        scroller.stack.push({
            obj: obj,
            offset: offset,
            inRange: inRange,
            outRange: outRange || false
        });
    }
    this.action = function(){
        scroller.stack.forEach(function(itm, i){
            if(scroller.isInRange(itm.obj, itm.offset)){
                itm.inRange(itm.obj);
                if(itm.outRange){
                    scroller.outStack.push(itm);
                }else{
                    delete scroller.stack[i];
                }
            }
        });
        scroller.outStack.forEach(function(itm, i){
            if(!scroller.isInRange(itm.obj, itm.offset)){
                itm.outRange(itm.obj);
                scroller.stack.push(itm);
            }
        });
    }
}