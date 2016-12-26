function Http(url,input,options){

    var self = this;

    this.responseType   = "text";//json
    this.timeout        = 0;


    //only self option
    this.____url        = url;
    this.____input      = input;
    this.____options    = options;

    this.ontimeout          = function(e,xhr){};
    this.onerror            = function(e,xhr){};
    this.onprogress         = function(e){};
    this.onsuccess          = function(responseText){}
    this.onreadystatechange = function(e,xhr){};
    this.beforesend         = function(xhr){}

    if( typeof options == "object" ) {
        for( var key in self.____options){
            this[key] = self.____options[key];
        }
    }

    var xhr = new XMLHttpRequest();

    xhr.responseType = this.responseType;
    xhr.timeout      = this.timeout;


    this.sendDataFormat = function(type){
        type = type.toLocaleLowerCase();
        var send_data = "";
        if( typeof self.____input == "object" ){
            if( type == "post" ) {
                send_data = new FormData();
                for (var key in self.____input) {
                    send_data.append(key, encodeURIComponent(self.____input[key]));
                }
            }else{
                var temp = [];
                for (var key in self.____input) {
                    temp.push(key+"="+encodeURIComponent(self.____input[key]));
                }
                send_data = temp.join("&");
            }
        }
        else{
            send_data = self.____input;
        }
        return send_data;
    };


    xhr.onload = function(e) {
        if((xhr.status >= 200 && xhr.status < 300) || xhr.status == 304){
            self.onsuccess(xhr.responseText);
        }else{
            self.onerror(e,xhr);
        }
    };
    xhr.ontimeout = function(e){
        self.ontimeout(e,xhr);
        self.onerror(e,xhr);
    };
    xhr.onerror   = function(e) {
        self.onerror(e,xhr);
    };


    // function updateProgress(event) {
    //     if (event.lengthComputable) {
    //         var completedPercent = event.loaded / event.total;
    //         self.onprogress( event );
    //     }
    // }

    xhr.onprogress        = self.onprogress;
    xhr.upload.onprogress = self.onprogress;


     xhr.onreadystatechange = function(e){
         self.onreadystatechange(e,xhr);
     };
     // function () {
    //     if (xhr.readyState == 4) {
    //         if (xhr.status == 200) {
    //             var data = JSON.parse(xhr.responseText);
    //             callback(data);
    //         } else {
    //             callback(null);
    //         }
    //     }
    //     alert("===>"+xhr.readyState+"==>"+xhr.responseText);
    // }



    this.post = function () {
        //构造表单数据
        var from_data = self.sendDataFormat("post");

        xhr.open('POST', self.____url, true);

        //必须在open之后调用
        //xhr.setRequestHeader('X-Test', 'one');
        //xhr.setRequestHeader('X-Test', 'two');
        self.beforesend(xhr);

        //发送数据
        xhr.send(from_data);
    };

    this.get = function(){

        var send_data = self.sendDataFormat("get");

        xhr.open('GET', self.____url, true);

        self.beforesend(xhr);

        xhr.send(send_data);
    };

    return {
        get:self.get,
        post:self.post
    };

}

chrome.extension.onConnect.addListener(function(port) {
    console.assert(port.name == "--wing-doc--");
    port.onMessage.addListener(function(data) {
        alert(data);
        //console.log(msg);

        // if (msg.joke == "Knock knock")
        //     port.postMessage({question: "Who's there?"});
        // else if (msg.answer == "Madame")
        //     port.postMessage({question: "Madame who?"});
        // else if (msg.answer == "Madame... Bovary")
        //     port.postMessage({question: "I don't get it."});
    });
});
