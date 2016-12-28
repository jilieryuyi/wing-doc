var WingDoc = chrome.extension.connect({name: "--wing-doc--"});

WingDoc.enable = function(){
    return $('meta[name="wingdoc"]').attr("enable") == "true";
}
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
    for( var i = 0; i < min_length; i++ ){
         str += Math.ceil( Math.random() * 9 );
    }
    var sub = max_length - min_length;
    if( sub <= 0 )
        return str;
    var r = Math.ceil(Math.random()*sub);
    for( var i = 0; i < r; i++ ){
         str += Math.ceil( Math.random() * 9 );
    }
    return str;
};

WingDoc.createString = function(min,max){
    if( max <= 0 )
        max = 128;

    var str = "";
    for ( var i=min;i<max;i++) {
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




$(document).ready(function(){

    console.assert( WingDoc.enable() == true,"not enable wingdoc" );
    console.log("enable wingdoc");

    $(".http-api-test-btn").on("click",function(){

        var tab   = $(this).parents(".request-tab");
        var urls  = tab.find(".visit-url");
        var len   = urls.length;
        var index = $(this).index();

        var request_datas = tab.find(".request-datas");
        var form_datas    = {};


        for ( var i = 0; i < len; i++ ){
            var url = urls.eq(i).text();
            request_datas.each(function(){
                var key  = $(this).children(".data-key").text();
                var type = $(this).children(".data-type").text();
                type = type.toLocaleLowerCase();

                var min  = $(this).children(".data-min").text();
                var max  = $(this).children(".data-max").text();

                //type类型 number string int float double
                switch( type )
                {
                    case "number":
                        form_datas[key] = WingDoc.createNumber(min,max);
                        break;
                    case "string":
                        form_datas[key] = WingDoc.createString(min,max);
                        break;
                    case "int":
                    case "float":
                    case "double":
                        form_datas[key] = WingDoc.createDigit(min,max);
                        break;
                }

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
                "responseType" : "json",
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
    // if (msg.question == "Who's there?")
    //     port.postMessage({answer: "Madame"});
    // else if (msg.question == "Madame who?")
    //     port.postMessage({answer: "Madame... Bovary"});
});

