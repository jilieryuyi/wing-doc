<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 17/3/4
 * Time: 19:37
 */
include __DIR__."/../vendor/autoload.php";
$html = new \Wing\Doc\Html("div");
$html->src = "a.png";
$html->class = "close";
$html->style = "display:none;";
$html->html = "hello";

echo $html->getTtml();