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
