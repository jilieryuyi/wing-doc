var WingDoc = chrome.extension.connect({name: "--wing-doc--"});

WingDoc.enable       = function(){
    var meta = $('meta[name="wingdoc"]');
        meta.attr("mounted",1);
    return meta.attr("enable") == "true";
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
WingDoc.dateFormat   = function(format){
    var min = 0;
    var max = 100*365*24*60*60;
    var time = Math.ceil(Math.random()*max);
    if( format == "int" ){
        return time;//parseInt((new Date().getTime())/1000);
    }

    return new WingDate(format,time).toString();

};
WingDoc.dateIncr     = function(format) {

};
$(document).ready(function(){

    if( !WingDoc.enable() )
    {
        return;
    }

    $(".http-api-test-btn").on("click",function(){

        var tab   = $(this).parents(".request-tab");
        var urls  = tab.find(".select-url:checked").parents(".visit-url");//.find("input:checked");
        var index = tab.attr("randc");

        var response_type_dom = tab.find(".request-response");
        var response_type     = "text";

        if( response_type_dom.length > 0 )
        {
            var rp = urls.children(".response").text();
            if( rp == "json" )
            {
                response_type = "json";
            }
        }

        var request_datas = tab.find(".request-datas");
        var form_datas    = {};


        var url = urls.find(".url").eq(0).text();
        console.log(url);
        request_datas.each(function(){
                var key  = $(this).children(".data-key").text();
                var type = $(this).children(".data-type").text();
                type = type.toLocaleLowerCase();

                var min  = $(this).children(".data-min").text();
                var max  = $(this).children(".data-max").text();
                var input = tab.find("."+key).eq(0);
                var inputchecked = tab.find(".data-type-"+key+":checked");
                var create_type = inputchecked.val();
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
                            var template    = $(this).find(".data-template").eq(0).text();
                            form_datas[key] = WingDoc.jsonFormat(template);
                        }
                            break;
                        case "datetime":{
                            var template    = $(this).find(".data-template").eq(0).text();
                            form_datas[key] = WingDoc.dateFormat(template);
                        }
                            break;
                    }
                }else if(create_type == 2){
                    var incr = inputchecked.parents("span").find(".incr").children("input").val();
                        incr = parseFloat(incr);
                    if( isNaN(incr))
                        incr = 1;
                    if( type == "string" ){
                        var v   = input.val();
                        var n   = v.match(/[\d]+/);
                        var num = 0;
                        if( n != null ) {
                            if (typeof n.length == "number" && n.length > 0)
                            {
                                num = n.pop();
                            }
                        }

                        v    = v.replace(num,"");
                        num  = parseInt(num);
                        num += incr;

                        form_datas[key]= v+num;

                    }
                    else if(type =="datetime"){
                        var template    = $(this).find(".data-template").eq(0).text();

                    }
                    else {
                        if (isNaN(parseFloat(input.val())))
                            form_datas[key] = 0;
                        else
                            form_datas[key] = parseFloat(input.val()) + incr;
                    }
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
         **/

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

    });
});



WingDoc.onMessage.addListener(function(data) {
    console.log(data);
    var dom     = $("."+data.index);
    var headers = dom.find(".result-headers");
    headers.html("");
    dom.find(".error").eq(0).html("");

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

