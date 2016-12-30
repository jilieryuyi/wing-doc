var WingDoc = chrome.extension.connect({name: "--wing-doc--"});

WingDoc.enable = function(){
    return $('meta[name="wingdoc"]').attr("enable") == "true";
};
//WingDoc.postMessage("hello wing doc");

// WingDoc.postMessage({joke: "Knock knock"});
// WingDoc.onMessage.addListener(function(msg) {
//     if (msg.question == "Who's there?")
//         port.postMessage({answer: "Madame"});
//     else if (msg.question == "Madame who?")
//         port.postMessage({answer: "Madame... Bovary"});
// });
var message_box_id = "---wing-doc---";
var message_box = document.createElement("div");
message_box.id=message_box_id;
message_box.style.display = "none";
document.body.appendChild(message_box);
//alert("run");

WingDoc.createNumber = function(min_length,max_length){
    var str = "";
    var i=0;
    for( i = 0; i < min_length; i++ ){
         str += Math.ceil( Math.random() * 9 );
    }
    var sub = max_length - min_length;
    if( sub <= 0 )
        return str;
    var r = Math.ceil(Math.random()*sub);
    for( i = 0; i < r; i++ ){
         str += Math.ceil( Math.random() * 9 );
    }
    return str;
};

WingDoc.createString = function(min,max){
    if( max <= 0 )
        max = 128;

    var str = "";
    var i = 0;
    for ( i = 0; i<min;i++) {
        str += String.fromCharCode(
             Math.ceil(Math.random() * 176)
        );
    }

    if( max == min )
        return str;

    var a = Math.ceil(Math.random() * (max-min));
    for ( i = 0; i < a; i++ ) {
        str += String.fromCharCode(
            Math.ceil(Math.random() * 176)
        );
    }

    return str;
};

WingDoc.createDigit = function(min,max){
    if( max <= 0 )
        max = 999999999;
    return parseInt(min)+Math.ceil(Math.random() * (max-min))
};

String.prototype.matchCallback=function(p,callback){
    var data = this.match(p);
    if( typeof data != "object" || data == null)
        return;

    console.log(typeof data);
    if( typeof data.length == "undefined")
        return;
    var len = data.length;
    for( var i=0; i<len; i++ ){
        callback(data[i]);
    }
};

function jsonFormat(json) {

    json.matchCallback(/\$\{[\s\S].+?\}/g, function (item) {
        var m = item.match(/[\w]+\([\s\S].+?\)/);
            m = m[0];


        var type = m.match(/[\w]+/);
        var r    = m.match(/[\d]+?/g);

        type = type[0];

        var min = 0;
        var max = 0;

        if (r.length == 1)
            min = max = r[0];
        else if (r.length == 2) {
            min = r[0];
            max = r[1];
        }

        var data = "";
        switch (type) {
            case "number":
                data = WingDoc.createNumber(min, max);
                break;
            case "string":
                data = WingDoc.createString(min, max);
                break;
            case "int":
            case "float":
            case "double":
                data = WingDoc.createDigit(min, max);
                break;
        }
        data = encodeURIComponent(data);
        data = data.replace(/%/g,"");
        json = json.replace(/\$\{[\s\S].+?\}/, data);

    });

    return json;
}


$(document).ready(function(){

    if( !WingDoc.enable() )
        return;
    //console.assert( WingDoc.enable() == true,"not enable wingdoc" );
    console.log("enable wingdoc");

    $(".http-api-test-btn").on("click",function(){

        var tab   = $(this).parents(".request-tab");
        var urls  = tab.find(".select-url:checked").parents(".visit-url");//.find("input:checked");
            //url   =
        //var len   = urls.length;
        var index = tab.attr("randc");

        var response_type_dom = tab.find(".request-response");
        var response_type = "text";
        if( response_type_dom.length > 0 )
        {
            var rp = urls.children(".response").text();
            if( rp == "json" )
                response_type = "json";
        }

        var request_datas = tab.find(".request-datas");
        var form_datas    = {};


        //for ( var i = 0; i < len; i++ )
        {
            var url = urls.find(".url").eq(0).text();
            console.log(url);
            request_datas.each(function(){
                var key  = $(this).children(".data-key").text();
                var type = $(this).children(".data-type").text();
                type = type.toLocaleLowerCase();

                var min  = $(this).children(".data-min").text();
                var max  = $(this).children(".data-max").text();
                var input = tab.find("."+key).eq(0);

                var create_type = tab.find(".data-type-"+key+":checked").val();
                if( create_type == 1 ) {
                    //type类型 number string int float double
                    switch (type) {
                        case "number":
                            form_datas[key] = WingDoc.createNumber(min, max);
                            break;
                        case "string":
                            form_datas[key] = WingDoc.createString(min, max);
                            break;
                        case "int":
                        case "float":
                        case "double":
                            form_datas[key] = WingDoc.createDigit(min, max);
                            break;
                        case "json": {
                            var template = $(this).next(".request-template").children(".data-template").text();
                            form_datas[key] = jsonFormat(template);
                        }
                            break;
                    }
                }else if(create_type == 2){
                    if( isNaN(parseFloat(input.val())))
                        form_datas[key] = 0;//parseFloat(input.val())+1;
                    else
                        form_datas[key] = parseFloat(input.val())+1;
                }else{
                    form_datas[key] = input.val();
                }

                input.val(form_datas[key]);

            });

            /**  responseType 的 可选值
                 ""	            String字符串	默认值(在不设置responseType时)
                 "text"	        String字符串
                 "document"	    Document对象	希望返回 XML 格式数据时使用
                 "json"	        javascript 对象	存在兼容性问题，IE10/IE11不支持
                 "blob"	        Blob对象
                 "arrayBuffer"	ArrayBuffer对象
             * */

            WingDoc.postMessage({
                "url"          : url,
                "data"         : form_datas,
                "index"        : index,
                "timeout"      : 3000,
                "responseType" : response_type,//"json",
                "headers"      : "", //设置header 如 {auth:123}
                "mimetype"     : ""
            });
            console.log(url,form_datas);
        }

    });
});

// document.addEventListener("WingDocPostMessage",function(event){
//     console.log(event);
//     WingDoc.postMessage(event.data);
// });

message_box.addEventListener('WingDocPostMessage', function() {
    var eventData = document.getElementById(message_box_id).innerText;
    WingDoc.postMessage(eventData);
});
WingDoc.onMessage.addListener(function(data) {
    console.log(data);
    var dom     = $("."+data.index);
    var headers = dom.find(".result-headers");
    headers.html("");
    var key ="";

    if( data.event == "onsuccess"){
        var value = typeof data.data == "object"?JSON.stringify(data.data):data.data;
        dom.find(".http-result").children("textarea").val(value);
        dom.find(".status").html(data.status);
        dom.find(".headers").html(data.headers_keys);

        for ( key in data.headers ){
            headers.append('<div><label class="hk">'+key+'</label><label class="hv">'+data.headers[key]+'</label></div>');
        }
    }
    else if( data.event == "onerror" ){
        dom.find(".status").html(data.status);
        dom.find(".headers").html(data.headers_keys);

        for ( key in data.headers ){
            headers.append('<div><label class="hk">'+key+'</label><label class="hv">'+data.headers[key]+'</label></div>');
        }
        dom.find(".error").eq(0).html("发生错误："+data.msg);
    }

    else if(data.event =="onprogress"){
        dom.find(".process").eq(0).animate({"width":(data.data*100)+"%"},1000);
    }
});

