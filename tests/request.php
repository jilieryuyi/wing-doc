<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/27
 * Time: 20:24
 */
$str = 'string(6,64) password 密码';
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
$max   = -1;

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

var_dump([
    "type" => $type,
    "key"  => $key,
    "doc"  => $doc,
    "min"  => $min,
    "max"  => $max
]);