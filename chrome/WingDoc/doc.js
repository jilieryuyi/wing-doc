var WingDoc = chrome.extension.connect({name: "--wing-doc--"});
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

var FormData = {
    createNumber:function(min_length,max_length){
        var str = "";
        for(var i=0;i<min_length;i++){
            str+=Math.ceil(Math.random()*9);
        }
        var sub = max_length-min_length;
        if( sub <= 0 )
            return str;
        var r = Math.ceil(Math.random()*sub);
        for(var i=0;i<r;i++){
            str+=Math.ceil(Math.random()*9);
        }
        return str;
    },
    createString:function(min,max){

    }
};

$(document).ready(function(){
    //alert("ready");
    $(".http-api-test-btn").on("click",function(){
        var tab  = $(this).parents(".request-tab");
        var urls = tab.find(".visit-url");
        var len  = urls.length;

        var request_datas = tab.find(".request-datas");
        var form_datas = {};
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
                    form_datas[key] = FormData.createNumber(min,max);
                    break;
                case "string":
                    form_datas[key] = FormData.createString(min,max);
                    break;
            }

        });

        for ( var i = 0; i < len; i++ ){
            console.log(urls.eq(i).text());
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
WingDoc.onMessage.addListener(function(msg) {
    // if (msg.question == "Who's there?")
    //     port.postMessage({answer: "Madame"});
    // else if (msg.question == "Madame who?")
    //     port.postMessage({answer: "Madame... Bovary"});
});

