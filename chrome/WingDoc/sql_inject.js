
if( typeof WingDoc == "undefined" )
    WingDoc = {};

WingDoc.sqlInject = function(){
    return [
        '1 or 1=1;--',
        '1" or "1"="1',
        '1" or "1"="1";--',
        '1\' or \'1\'=\'1',
        '1\' or \'1\'=\'1\';--'
    ];
};