function Http(url,input,options){

    var self = this;

    this.responseType   = "text";//json
    this.timeout        = 0;


    //only self option
    this.____url        = url;
    this.____input      = input;
    this.____options    = options;
    this.____headers    = {};
    this.____mimetype   = "";

    if( typeof options.headers == "object"  )
        this.____headers = options.headers;

    if( typeof options.mimetype != "undefined" )
        this.____mimetype = options.mimetype;

    this.ontimeout          = function(e,xhr){};
    this.onerror            = function(e,xhr){};
    this.onprogress         = function(e){};
    this.onsuccess          = function(responseText,xhr){}
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
            var response = "";
            if( self.responseType == "json" )
                response = xhr.response;
            else if( self.responseType == "document")
            {
                response = xhr.responseXML;//"";//xhr.responseText;
            }else{
                response = xhr.responseText;
            }

            self.onsuccess(response,xhr);
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
        for( var key in self.____headers )
            xhr.setRequestHeader(key,self.____headers[key]);


        if( self.____mimetype != "" )
            xhr.overrideMimeType(self.____mimetype);

        self.beforesend(xhr);

        //发送数据
        xhr.send(from_data);
    };

    this.get = function(){

        var send_data = self.sendDataFormat("get");

        xhr.open('GET', self.____url, true);

        for( var key in self.____headers )
            xhr.setRequestHeader(key,self.____headers[key]);

        if( self.____mimetype != "" )
            xhr.overrideMimeType(self.____mimetype);

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
        // {
        //     "url"   : url,
        //     "data"  : form_datas,
        //     "index" : index
        // }

        var timeout = 0;
        if( typeof data.timeout != "undefined" )
            timeout = data.timeout;

        var responseType = "text";
        if( typeof data.responseType != "undefined" )
            responseType = data.responseType;

        var headers = {};
        if( typeof data.headers == "object" )
            headers = data.headers;

        var mimetype = "";
        if( typeof data.mimetype != "undefined" )
            mimetype = data.mimetype;
        //alert(1);
        var http = new Http(data.url,data.data,{

            timeout      : timeout,
            responseType : responseType,
            headers      : headers,
            mimetype     : mimetype,

            ontimeout : function(e,xhr){
                port.postMessage({
                    index : data.index,
                    error : "time out",
                    event : "ontimeout"
                });
            },
            onerror   : function(e,xhr){
                port.postMessage({
                    index : data.index,
                    status: xhr.status,
                    error : xhr.statusText,
                    event : "onerror"
                });
            },
            onprogress: function(e){
                if ( event.lengthComputable ) {
                    var completedPercent = event.loaded / event.total;
                    port.postMessage({
                        index  : data.index,
                        data   : completedPercent,
                        error  : "on process",
                        event  : "onprogress"
                    });
                }
            },
            onsuccess : function(responseText,xhr){
                var str     = xhr.getAllResponseHeaders();
                var arr     = str.split("\r\n");
                var headers = {};



                arr.map(function(header_str){

                    if( header_str.indexOf(":") < 0 )
                        return;

                    var temp = header_str.split(":");
                    temp[0]  = temp[0].replace(/(^\s)|(\s$)/g, "");
                    temp[1]  = temp[1].replace(/(^\s)|(\s$)/g, "");

                    if( temp[0].length <= 0 )
                        return;

                    headers[temp[0]] = temp[1];
                });

                port.postMessage({
                    index  : data.index,
                    status : xhr.status,
                    statusText : xhr.statusText,
                    headers: headers,
                    data   : responseText,
                    error  : "ok",
                    event  : "onsuccess"
                });
            },
            onreadystatechange : function(e,xhr){

                var status_text = "";
                switch(xhr.readyState){
                    case 1://OPENED
                        status_text = "opened";
                        break;
                    case 2://HEADERS_RECEIVED
                        status_text = "headers_receive";

                        break;
                    case 3://LOADING
                        //do something
                        status_text = "loading";

                        break;
                    case 4://DONE
                        //do something
                        status_text = "done";

                        break;
                }

                port.postMessage({
                    index      : data.index,
                    data       : xhr.readyState,
                    readyState : xhr.readyState,
                    statusText : xhr.statusText,
                    status     : xhr.status,
                    error      : status_text,
                    event      : "onreadystatechange"
                });
            },
            beforesend         : function(xhr){
                port.postMessage({
                    index  : data.index,
                    error  : "before send",
                    event  : "beforesend"
                });
            }
        });
        http.post();
        //console.log(msg);

        // if (msg.joke == "Knock knock")
        //     port.postMessage({question: "Who's there?"});
        // else if (msg.answer == "Madame")
        //     port.postMessage({question: "Madame who?"});
        // else if (msg.answer == "Madame... Bovary")
        //     port.postMessage({question: "I don't get it."});
    });
});
