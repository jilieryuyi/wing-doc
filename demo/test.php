<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/24
 * Time: 07:01
 */
include "../Doc.php";
include "../WClass.php";
include "../WDoc.php";
include "../WFile.php";
include "../WFunction.php";


$app = new \Wing\Doc\Doc("/Users/yuyi/Web/activity/app/Logic/lib2",__DIR__."/doc");
$app->run();