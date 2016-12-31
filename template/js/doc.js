var WingDoc = {};

WingDoc.mounted      = function(){
    return $('meta[name="wingdoc"]').attr("mounted") == "1";
};
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
WingDoc.createDigit  = function(min,max){
    if( max <= 0 )
        max = 999999999;
    return parseInt(min)+Math.ceil(Math.random() * (max-min))
};
WingDoc.jsonFormat   = function(json) {

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
};
WingDoc.onMessage    = function(data) {
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
};
WingDoc.send         = function(data) {

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
            WingDoc.onMessage({
                index : data.index,
                error : "time out",
                event : "ontimeout"
            });
        },
        onerror   : function(e,xhr,msg){
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

            WingDoc.onMessage({
                index : data.index,
                headers: headers,
                headers_keys:headers_keys,
                xhr:xhr,
                e:e,
                status: xhr.status,
                error : xhr.statusText,
                event : "onerror",
                msg:msg
            });
        },
        onprogress: function(e){
            if ( event.lengthComputable ) {
                var completedPercent = event.loaded / event.total;
                WingDoc.onMessage({
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

            WingDoc.onMessage({
                index  : data.index,
                status : xhr.status,
                statusText : xhr.statusText,
                headers: headers,
                headers_keys:headers_keys,

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

            WingDoc.onMessage({
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
            WingDoc.onMessage({
                index  : data.index,
                error  : "before send",
                event  : "beforesend"
            });
        }
    });
    http.post();
};

$(document).ready(function(){

    //延迟绑定
    window.setTimeout(function() {

        //如果安装了扩展 默认使用扩展支持
        if (WingDoc.mounted()) {
            console.log("使用扩展");
            return;
        }

        $(".http-api-test-btn").on("click", function () {

            var tab = $(this).parents(".request-tab");
            var urls = tab.find(".select-url:checked").parents(".visit-url");//.find("input:checked");
            var index = tab.attr("randc");

            var response_type_dom = tab.find(".request-response");
            var response_type = "text";
            if (response_type_dom.length > 0) {
                var rp = urls.children(".response").text();
                if (rp == "json")
                    response_type = "json";
            }

            var request_datas = tab.find(".request-datas");
            var form_datas    = {};
            var url           = urls.find(".url").eq(0).text();

            request_datas.each(function () {
                var key = $(this).children(".data-key").text();
                var type = $(this).children(".data-type").text();
                type = type.toLocaleLowerCase();

                var min = $(this).children(".data-min").text();
                var max = $(this).children(".data-max").text();
                var input = tab.find("." + key).eq(0);

                var create_type = tab.find(".data-type-" + key + ":checked").val();
                if (create_type == 1) {
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
                            form_datas[key] = WingDoc.jsonFormat(template);
                        }
                            break;
                    }
                } else if (create_type == 2) {
                    if (isNaN(parseFloat(input.val())))
                        form_datas[key] = 0;//parseFloat(input.val())+1;
                    else
                        form_datas[key] = parseFloat(input.val()) + 1;
                } else {
                    form_datas[key] = input.val();
                }

                input.val(form_datas[key]);

            });

            WingDoc.send({
                "url": url,
                "data": form_datas,
                "index": index,
                "timeout": 3000,
                "responseType": response_type,//"json",
                "headers": "", //设置header 如 {auth:123}
                "mimetype": ""
            });
        });
    },1000);
});


