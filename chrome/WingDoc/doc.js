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
alert("run");
$(document).ready(function(){
    alert("ready");
    $(".http-api-test-btn").on("click",function(){
        var tab = $(this).parents(".request-tab");
        alert(1);
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

