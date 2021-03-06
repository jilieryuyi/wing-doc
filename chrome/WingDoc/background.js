var WingDoc = {};
WingDoc.Http = function(url,input,options){

    var self            = this;
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
    this.onerror            = function(e,xhr,msg){};
    this.onprogress         = function(e){};
    this.onsuccess          = function(responseText,xhr){};
    this.onreadystatechange = function(e,xhr){};
    this.beforesend         = function(xhr){};
    var key                 = "";

    if( typeof options == "object" ) {
        for( key in self.____options){
            if( self.____options.hasOwnProperty(key) )
            {
                this[key] = self.____options[key];
            }
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
                for (key in self.____input) {
                    if( self.____input.hasOwnProperty(key) )
                    {
                        send_data.append(key, encodeURIComponent(self.____input[key]));
                    }
                }
            }else{
                var temp = [];
                for ( key in self.____input) {
                    if( self.____input.hasOwnProperty(key))
                    {
                        temp.push(key+"="+encodeURIComponent(self.____input[key]));
                    }
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
            self.onerror(e,xhr,"request error");
        }
    };
    xhr.ontimeout = function(e){
        self.ontimeout(e,xhr);
        self.onerror(e,xhr,"time out");
    };
    xhr.onerror   = function(e) {
        self.onerror(e,xhr,"error");
    };

    xhr.onprogress        = self.onprogress;
    xhr.upload.onprogress = self.onprogress;


     xhr.onreadystatechange = function(e){
         self.onreadystatechange(e,xhr);
     };

    this.post = function () {
        //构造表单数据
        var from_data = self.sendDataFormat("post");

        xhr.open('POST', self.____url, true);

        //必须在open之后调用
        for(var key in self.____headers )
        {
            if( self.____headers.hasOwnProperty(key))
            {
                xhr.setRequestHeader(key,self.____headers[key]);
            }
        }


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
        {
            if( self.____headers.hasOwnProperty(key) )
            {
                xhr.setRequestHeader(key,self.____headers[key]);
            }
        }

        if( self.____mimetype != "" )
            xhr.overrideMimeType(self.____mimetype);

        self.beforesend(xhr);

        xhr.send(send_data);
    };

    this.getHeaders = function(){
        var str     = xhr.getAllResponseHeaders();
        var arr     = str.split("\r\n");
        var headers = {};

        var headers_keys = 0;

        arr.map(function(header_str){

            if( header_str.indexOf(":") < 0 )
                return;

            var temp = header_str.split(":");
            temp[0]  = temp[0].replace(/(^\s)|(\s$)/g, "");
            temp[1]  = temp[1].replace(/(^\s)|(\s$)/g, "");

            if( temp[0].length <= 0 )
                return;

            headers[temp[0]] = temp[1];
            headers_keys++;
        });

        return {
            headers:headers,
            count:headers_keys
        };

    };

    return {
        getHeaders:self.getHeaders,
        get:self.get,
        post:self.post
    };

};
WingDoc.getAllHeaders = function(xhr){
    var str     = xhr.getAllResponseHeaders();
    var arr     = str.split("\r\n");
    var headers = {};

    var headers_keys = 0;

    arr.map(function(header_str){

        if( header_str.indexOf(":") < 0 )
            return;

        var temp = header_str.split(":");
        temp[0]  = temp[0].replace(/(^\s)|(\s$)/g, "");
        temp[1]  = temp[1].replace(/(^\s)|(\s$)/g, "");

        if( temp[0].length <= 0 )
            return;

        headers[temp[0]] = temp[1];
        headers_keys++;
    });
    return {
        headers:headers,
        count:headers_keys
    };
};
chrome.extension.onConnect.addListener(function(port) {
    if( port.name != "--wing-doc--" )
    {
        return;
    }
    port.onMessage.addListener(function(data) {

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

        var response = {
            start:new Date().getTime(),
            class:data.class,
            index:data.index
        };

        var http = new WingDoc.Http(data.url,data.data,{

            timeout      : timeout,
            responseType : responseType,
            headers      : headers,
            mimetype     : mimetype,

            ontimeout : function(e,xhr){
                response.error = "time out";
                response.event = "ontimeout";
                response.end   = new Date().getTime();
                port.postMessage(response);
                console.log(e,xhr);
            },

            onerror   : function(e,xhr,msg){
                var headers = WingDoc.getAllHeaders(xhr);
                var append = {
                    headers      : headers.headers,
                    headers_keys : headers.count,
                    xhr          : xhr,
                    e            : e,
                    status       : xhr.status,
                    error        : xhr.statusText,
                    event        : "onerror",
                    msg          : msg,
                    end           :new Date().getTime()
                };
                for (var k in append){
                    response[k] = append[k];
                }
                port.postMessage(response);
            },
            onprogress: function(e){
                if ( event.lengthComputable ) {
                    var completedPercent = event.loaded / event.total;
                    var append = {
                        data   : completedPercent,
                        error  : "on process",
                        event  : "onprogress",
                        end           :new Date().getTime()

                    };
                    for (var k in append){
                        response[k] = append[k];
                    }
                    port.postMessage(append);
                    console.log(e);
                }
            },
            onsuccess : function(responseText,xhr){
                var headers = WingDoc.getAllHeaders(xhr);
                var append = {
                    status       : xhr.status,
                    statusText   : xhr.statusText,
                    headers      : headers.headers,
                    headers_keys : headers.count,
                    data         : responseText,
                    error        : "ok",
                    event        : "onsuccess",
                    end           :new Date().getTime()

                };
                for (var k in append){
                    response[k] = append[k];
                }
                port.postMessage(response);
            },
            onreadystatechange : function(e,xhr){

                var status_text = "";
                switch( xhr.readyState ){
                    case 1:
                        status_text = "opened";
                        break;
                    case 2:
                        status_text = "headers_receive";
                        break;
                    case 3:
                        status_text = "loading";
                        break;
                    case 4:
                        status_text = "done";
                        break;
                }
                var append = {
                    data       : xhr.readyState,
                    readyState : xhr.readyState,
                    statusText : xhr.statusText,
                    status     : xhr.status,
                    error      : status_text,
                    event      : "onreadystatechange",
                    end           :new Date().getTime()

                };
                for (var k in append){
                    response[k] = append[k];
                }
                port.postMessage(response);
            },
            beforesend         : function(xhr){
                var append = {
                    error  : "before send",
                    event  : "beforesend",
                    end           :new Date().getTime()

                };
                for (var k in append){
                    response[k] = append[k];
                }
                port.postMessage(response);
                console.log(xhr);
            }
        });
        http.post();
    });
});

chrome.webRequest.onBeforeRequest.addListener(function interceptRequest(request) {
    var url = decodeURIComponent(request.url);

    //alert(url);
    /*var fileName = getQueryString(url, "fileName"),
        layer = getQueryString(url, "layer"),
        line = getQueryString(url, "line"),
        row = getQueryString(url, "row");
    var newUrl = fileName + "?layer=" + layer + "&line=" + line + "&row=" + row;
*/

   /* for (var i = 0; i < request.requestHeaders.length; ++i) {
        if (details.requestHeaders[i].name === 'User-Agent') {
            details.requestHeaders.splice(i, 1);
            break;
        }
    }
    return {requestHeaders: details.requestHeaders};*/

    console.log("wing-->",request);
    //https://chajian.baidu.com/developer/extensions/webRequest.html
    //return { redirectUrl: "http://www.itdfy.com/" };
}, { urls: ["<all_urls>"] }, ['blocking'/*, "requestHeaders"*/]);
