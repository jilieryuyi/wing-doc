<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/27
 * Time: 20:24
 */
$str = 'string(6,64) password 密码';
$str = 'json topics
      [{"id":0,"name":"{string(0,0)}"},{"id":0,"name":"{string(0,0)}"}]
      悬赏话题';

$str = ' datetime time ${Y-m-d H:i:s} 时间';

$str = trim($str);

preg_match("/\([\S\s]{1,}\)/",$str,$match);
if( count($match) > 0 ){
    $match = preg_replace("/[\s]+/","",$match[0]);
    $str   = preg_replace("/\([\S\s]{1,}\)/",$match, $str);
}

echo $str;

$match = preg_split("/[\s]+/",$str,3);
$type  = "string";
$key   = "";
$doc   = "";
$min   = 0;
$max   = 0;

if( count( $match ) == 3 ){
    $type = $match[0];
    $key = $match[1];
    $doc = $match[2];

    preg_match_all("/[\d]+/",$type,$range);
var_dump($range);
    if( count($range[0]) == 1 )
        $min = $max = $range[0][0];
    else{
        $min = $range[0][0];
        $max = $range[0][1];
    }

}else{
    $key = $match[0];
    $doc = $match[1];
}

$format = "";

if( $type == "json" ){
    //$doc =' {"id":0,"name":"123"} srdfsdf';
    preg_match("/(\[|\{)[\s\S]{1,}(\}|\])+/",$doc,$jmatch);
    var_dump($jmatch);
    $format = $jmatch[0];
}
else if( $type == "datetime" ){

    echo $doc,"\r\n";
    preg_match("/\\$\{[\s\S]{1,}\}/",$doc,$dmatch);
    var_dump("-------->",$dmatch);

    if( isset($dmatch[0]) )
    {
        $format = $dmatch[0];
        $format = ltrim($format,"$");
        $format = ltrim($format,"{");
        $format = rtrim($format,"}");
        $format = trim($format);
        $doc    = preg_replace("/\\$\{[\s\S]{1,}\}/","",$doc);
        $doc    = trim($doc);
    }
    else
    {
        $format = "int";
    }

}

var_dump([
    "type" => $type,
    "key"  => $key,
    "doc"  => $doc,
    "min"  => $min,
    "max"  => $max,
    "format" => $format
]);