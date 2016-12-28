<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/27
 * Time: 20:24
 */
$str = 'number phone 注册手机号码';

preg_match("/\([\S\s]{1,}\)/",$str,$match);
if( count($match) > 0 ){
    $match = preg_replace("/[\s]+/","",$match[0]);
    $str   = preg_replace("/\([\S\s]{1,}\)/",$match, $str);
}

echo $str;

$match = preg_split("/[\s]+/",$str,3);
var_dump($match);