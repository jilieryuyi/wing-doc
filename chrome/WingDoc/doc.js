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
        var tab_class = tab.attr("randc");
        WingDoc[tab_class] = 0;

        var test_times = tab.find(".test-times").eq(0).children("input").val();
        test_times = parseInt(test_times);
        if(isNaN(test_times)||test_times<=0)
            test_times = 1;

        var timeout = tab.find(".timeout").eq(0).children("input").val();
        timeout = parseInt(timeout);
        if(isNaN(timeout))
            timeout = 0;

        var limit = tab.find(".limit").eq(0).children("input").val();
        limit = parseInt(limit);
        if(isNaN(limit))
            limit = 0;

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


        var url = urls.find(".url").eq(0).text();
        console.log(url);

        for( var t=0;t<test_times;t++) {

            (function(timeout,t){
                window.setTimeout(function(){
                    var form_datas    = {};
                    request_datas.each(function () {
                        var key = $(this).children(".data-key").text();
                        var type = $(this).children(".data-type").text();
                        type = type.toLocaleLowerCase();

                        var min = $(this).children(".data-min").text();
                        var max = $(this).children(".data-max").text();
                        var input = tab.find("." + key).eq(0);
                        var inputchecked = tab.find(".data-type-" + key + ":checked");
                        var create_type = inputchecked.val();
                        if (create_type == 1) {
                            //type类型 number string int float double
                            switch (type) {
                                case "number":
                                    form_datas[key] = encodeURIComponent(WingDoc.createNumber(min, max));
                                    break;
                                case "string":
                                    form_datas[key] = encodeURIComponent(WingDoc.createString(min, max));
                                    break;
                                case "int":
                                case "float":
                                case "double":
                                    form_datas[key] = encodeURIComponent(WingDoc.createDigit(min, max));
                                    break;
                                case "json": {
                                    var template = $(this).find(".data-template").eq(0).text();
                                    form_datas[key] = encodeURIComponent(WingDoc.jsonFormat(template));
                                }
                                    break;
                                case "datetime": {
                                    var template = $(this).find(".data-template").eq(0).text();
                                    form_datas[key] = encodeURIComponent(WingDoc.dateFormat(template));
                                }
                                    break;
                            }
                        } else if (create_type == 2) {
                            var incr = inputchecked.parents("span").find(".incr").children("input").val();
                            incr = parseFloat(incr);
                            if (isNaN(incr))
                                incr = 1;
                            if (type == "string") {
                                var v = input.val();
                                var n = v.match(/[\d]+/);
                                var num = 0;
                                if (n != null) {
                                    if (typeof n.length == "number" && n.length > 0) {
                                        num = n.pop();
                                    }
                                }

                                v = v.replace(num, "");
                                num = parseInt(num);
                                num += incr;

                                form_datas[key] = encodeURIComponent(v + num);

                            }
                            else if (type == "datetime") {
                                var template = $(this).find(".data-template").eq(0).text();

                            }
                            else {
                                if (isNaN(parseFloat(input.val())))
                                    form_datas[key] = 0;
                                else
                                    form_datas[key] = parseFloat(input.val()) + incr;
                            }
                        } else {
                            form_datas[key] = encodeURIComponent(input.val());
                        }

                        input.val(form_datas[key]);

                    });

                    console.log(url, form_datas);


                    console.log("=========>",form_datas);

                    /**  responseType 的 可选值
                     ""                String字符串    默认值(在不设置responseType时)
                     "text"            String字符串
                     "document"        Document对象    希望返回 XML 格式数据时使用
                     "json"            javascript 对象    存在兼容性问题，IE10/IE11不支持
                     "blob"            Blob对象
                     "arrayBuffer"    ArrayBuffer对象
                     **/

                    var request_times = tab.find(".request-times").text();
                    request_times = parseInt(request_times) + 1;
                    tab.find(".request-times").html(request_times);
                    var rindex =t+1;
                    var rdata = {
                        "url": url,
                        "data": form_datas,
                        "class": tab_class,
                        "index":rindex,
                        "start":new Date().getTime(),
                        "timeout": timeout,
                        "responseType": response_type,//"json",
                        "headers": "", //设置header 如 {auth:123}
                        "mimetype": ""
                    };
                    console.log("=========>",rdata);

                    console.log("------------->post",rdata);
                    WingDoc.postMessage(rdata);
                },timeout);
            })(limit*t,t);

        }

    });
    $(".http-api-clear-btn").on("click",function () {
        var tab   = $(this).parents(".request-tab");
        var urls  = tab.find(".select-url:checked").parents(".visit-url");//.find("input:checked");
        var tab_class = tab.attr("randc");
        WingDoc[tab_class] = 0;

        tab.find(".http-result").children("textarea").val("");
        tab.find(".status").html(0);
        tab.find(".headers").html(0);
        tab.find(".success-times").html(0);

        tab.find(".span-times").html(0);
        tab.find(".error-times").html(0);
        tab.find(".error").eq(0).html("");
        tab.find(".request-times").html(0);


    });
});



WingDoc.onMessage.addListener(function(data) {
    console.log(data);
    var dom     = $("."+data.class);
    var headers = dom.find(".result-headers");

    var error_times = dom.find(".error-times").text();
    error_times     = parseInt(error_times);


    var success_times = dom.find(".success-times").text();
    success_times     = parseInt(success_times);


    headers.html("");
    dom.find(".error").eq(0).html("");

    var key ="";

    if( data.event == "onsuccess"){
        WingDoc[data.class] = parseInt(WingDoc[data.class])+(data.end) - (data.start);
        var value = typeof data.data == "object"?JSON.stringify(data.data):data.data;
        dom.find(".http-result").children("textarea").val(value);
        dom.find(".status").html(data.status);
        dom.find(".headers").html(data.headers_keys);

        for ( key in data.headers ){
            headers.append('<div><label class="hk">'+key+'</label><label class="hv">'+data.headers[key]+'</label></div>');
        }

        //console.log("=========>", WingDoc[data.class],parseInt(WingDoc[data.class]/parseInt(data.index)));
        dom.find(".success-times").html(success_times+1);

        dom.find(".span-times").html(parseInt(WingDoc[data.class]/data.index));

    }
    else if( data.event == "onerror" ){
        WingDoc[data.class] = parseInt(WingDoc[data.class])+(data.end) - (data.start);

        dom.find(".error-times").html(error_times+1);
        dom.find(".status").html(data.status);
        dom.find(".headers").html(data.headers_keys);

        for ( key in data.headers ){
            headers.append('<div><label class="hk">'+key+'</label><label class="hv">'+data.headers[key]+'</label></div>');
        }
        dom.find(".error").eq(0).html("发生错误："+data.msg);
        console.log("=========>", WingDoc[data.class],parseInt(WingDoc[data.class]/data.index));

        dom.find(".span-times").html(parseInt(WingDoc[data.class]/data.index));

    }

    else if(data.event =="onprogress"){
        dom.find(".process").eq(0).animate({"width":(data.data*100)+"%"},1000);
    }
});

