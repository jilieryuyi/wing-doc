<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/28
 * Time: 22:46
 */

$str = '[{"id":0,"name":"123"},{"id":0,"name":"456"}]';

$data = json_decode($str,true);
var_dump($data);

//echo json_encode(["id"=>0,"name"=>"123"]);
//    [
//    ["id"=>0,"name"=>"test1"],
//    ["id"=>0,"name"=>"test2"]
//]);