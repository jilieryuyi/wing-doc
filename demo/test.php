<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/24
 * Time: 07:01
 */
include __DIR__."/../Doc.php";
include __DIR__."/../WClass.php";
include __DIR__."/../WDoc.php";
include __DIR__."/../WFile.php";
include __DIR__."/../WFunction.php";
include __DIR__."/../WDir.php";



$app = new \Wing\Doc\Doc("/Users/yuyi/Web/activity/app/Logic/lib2",__DIR__."/doc");
$app->run();