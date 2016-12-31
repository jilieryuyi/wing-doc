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
    this.onerror            = function(e,xhr,msg){};
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

}
