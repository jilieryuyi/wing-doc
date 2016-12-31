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
String.prototype.replaceCallback = function(p,callback){
    var data = this.match(p);
    if( typeof data != "object" || data == null)
        return;

    console.log(typeof data);
    if( typeof data.length == "undefined")
        return;
    var len = data.length;

    var str = this;

    for( var i=0; i<len; i++ ){
        var ns = callback(data[i]);
        str = str.replace(data[i],ns);
    }
    return str;
};

